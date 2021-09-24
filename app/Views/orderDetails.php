<?php include 'template/header.php'; ?>
    <main class="main-content">
      <div class="container">
        <div class="row mb-2">
            <div class="col-md-6 col-sm-6 col-5">
                <h5>Order Details &nbsp;<img src="<?= getenv('app.ASSETSPATH') ?>images/refresh.svg" id="refreshButton" style="height:3%;width:3%;margin-top: -8px;"></h5>
            </div>
            <div class="col-md-6 text-end col-sm-6 col-7 back-button">
                <a href="<?= getenv('app.baseURL') ?>" ><h5 style="font-size: 18px;"><i class="fas fa-arrow-left me-2"></i>Back to dashboard</h5></a>
            </div>
        </div>
		<div class="row">
			<?php $totalPages = ceil($count / $limit);
            ?>
			<?= pagination($offset, $totalPages) ?>
        </div>
        <div class="row top-bar order-srch border-top border-right">
          <div class="col-md-12 col-sm-8 col-8 top-search">
             <div class="input-group ">
             <span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control search-input rounded-end" id="searchVal" value="<?= $searchVal ?>" placeholder="Search">
             </div>
          </div>
        </div>
        <div class="table-responsive">
            <table class="table" id="myTable">
				<thead class="cf">
                  <tr class="header">
                    <th>Revolut Order ID</th>
					<th>Bigcommerce<br/>order id</th>
					<th>Capture <br> type</th>
					<th>Payment<br> status</th>
					<th>Settlement<br> status</th>
					<th>Currency</th>
					<th>Total</th>
					<th>Amount Paid</th>
					<th>Created Date</th>
					<th>Actions</th>
                  </tr>
				</thead>
                <tbody id="table_data_rows">
					<?php
                        if (count($orderDetails) > 0) {
                            foreach ($orderDetails as $k => $values) {
                                ?>
					  <tr>
						<td>
							<?php
                                $api_response = json_decode(stripslashes($values['api_response']), true);
                                if (isset($api_response['id'])) {
                                    echo $api_response['id'];
                                } else {
                                    echo $values['invoice_id'];
                                } ?>
						</td>
						<td><?= $values['order_id'] ?></td>
						<td><?= $values['type'] ?></td>
						<td>
							<?php
                                $status = '';
                                if ($values['status'] === 'COMPLETED') {
                                    $status = '<span class="badge bg-success table-status-clr">Completed</span>';
                                } else {
                                    $status = '<span class="badge btn-pink table-status-clr">' . ucfirst(strtolower($values['status'])) . '</span>';
                                } ?>
							<?= $status ?>
						</td>
						<td>
							<?php
                                $sstatus = '';
                                if ($values['type'] === 'AUTOMATIC') {
                                    if ($values['status'] === 'COMPLETED') {
                                        if ($values['settlement_status'] === 'REFUND') {
                                            $sstatus = '<span class="badge bg-success table-status-clr">' . ucfirst($values['settlement_status']) . '</span>';
                                        } else {
                                            $sstatus = '<span class="badge bg-success table-status-clr">Completed</span>';
                                        }
                                    } else {
                                        $sstatus = '<span class="badge btn-pink table-status-clr">' . ucfirst(strtolower($values['settlement_status'])) . '</span>';
                                    }
                                } else {
                                    if (($values['settlement_status'] === 'COMPLETED') || ($values['settlement_status'] === 'REFUND')) {
                                        $sstatus = '<span class="badge bg-success table-status-clr">' . ucfirst(strtolower($values['settlement_status'])) . '</span>';
                                    } else {
                                        $sstatus = '<span class="badge btn-pink table-status-clr">' . ucfirst(strtolower($values['settlement_status'])) . '</span>';
                                    }
                                } ?>
							<?= $sstatus ?>
						</td>
						<td><?= $values['currency'] ?></td>
						<td><?= sprintf('%.2f', $values['total_amount']) ?></td>
						<td><?= sprintf('%.2f', ($values['amount_paid'] / 100)) ?></td>
						<td><?= date('Y-m-d h:i A', strtotime($values['created_at'])) ?></td>
						<td>
							<?php
                                $orderRefund = new \App\Models\OrderRefundModel();
                                $condition   = [
                                    'invoice_id'          => $values['invoice_id'],
                                    'refund_status'       => 'COMPLETED'
                                ];

                                $db_resp         = $orderRefund->getAllData($condition);

                                $refunded_amount = 0;
                                $total_amount    = $values['total_amount'];
                                if ($db_resp['status']) {
                                    $ref_result = $db_resp['data'];
                                    foreach ($ref_result as $k => $v) {
                                        if ($v['refund_status'] === 'COMPLETED') {
                                            $refunded_amount += $v['refund_amount'];
                                        }
                                    }
                                }
                                $refund = 'Refund';
                                if (($total_amount - $refunded_amount) > 0) {
                                    if ($refunded_amount > 0) {
                                        $refund = 'Partial Refund';
                                    }
                                }
                                $actions = '';
                                if ($values['status'] === 'AUTHORISED' && $values['type'] === 'MANUAL' && ($values['settlement_status'] === 'PENDING' || $values['settlement_status'] === 'FAILED')) {
                                    $actions .= '<a href="' . getenv('app.baseURL') . 'settleOrder/index/' . base64_encode(json_encode($values['invoice_id'])) . '" ><button type="button" class="btn btn-outline-danger com-btn sm-margin">Capture</button></a>';
                                } elseif ($values['status'] === 'COMPLETED' && $values['type'] === 'MANUAL' && ($values['settlement_status'] === 'COMPLETED' || $values['settlement_status'] === 'REFUND')) {
                                    $actions .= '<button type="button" class="btn btn-danger com-btn sm-margin" disabled >Captured</button>';

                                    if (($total_amount - $refunded_amount) > 0) {
                                        $actions .= '<a href="' . getenv('app.baseURL') . 'refundOrder/index/' . base64_encode(json_encode($values['invoice_id'])) . '" ><button type="button" class="btn btn-outline-success com-btn sm-margin">' . $refund . '</button></a>';
                                    } else {
                                        $actions .= '<button type="button" class="btn btn-success com-btn sm-margin" disabled >Refunded</button>';
                                    }
                                } elseif ($values['status'] === 'COMPLETED') {
                                    if (($total_amount - $refunded_amount) > 0) {
                                        $actions .= '<a href="' . getenv('app.baseURL') . 'refundOrder/index/' . base64_encode(json_encode($values['invoice_id'])) . '" ><button style="width: 100%;" type="button" class="btn btn-outline-success com-btn sm-margin">' . $refund . '</button></a>';
                                    } else {
                                        $actions .= '<button type="button" class="btn btn-success com-btn sm-margin" disabled >Refunded</a></button>';
                                    }
                                } ?>
							<?= $actions ?>
						</td>
					  </tr>
					<?php
                            }
                        } ?>
				</tbody>
            </table>
        </div>
      </div>
    </main>
	<?php include 'template/footer.php'; ?>
	<script type="text/javascript" charset="utf8" src="<?= getenv('app.ASSETSPATH') ?>js/datatable/jquery.dataTables.min.js"></script>
	<script type="text/javascript" charset="utf8" src="<?= getenv('app.ASSETSPATH') ?>js/datatable/datatable-responsive.js"></script>
     <script src="<?= getenv('app.ASSETSPATH') ?>js/order-details.js?v=1.00"></script>
    <script>
		var text = "Please wait...";
		var current_effect = "bounce";
		var app_base_url = "<?= getenv('app.baseURL') ?>";
		var poffset = "<?= $offset ?>";
		var limit = "<?= $limit ?>";
		$('body').on('click','#refreshButton',function(){
			$("body").waitMe({
				effect: current_effect,
				text: text,
				bg: "rgba(255,255,255,0.7)",
				color: "#000",
				maxSize: "",
				waitTime: -1,
				source: "images/img.svg",
				textPos: "vertical",
				fontSize: "",
				onClose: function(el) {}
			});
			var url = app_base_url+'home/orderDetails';
			window.location.href = url;
		});
		$('body').on('click','.ImagePagination',function(){
			var offset = $(this).attr('data-page');
			if(parseInt(offset) != parseInt(poffset)){
				var search_val = $('body #searchVal').val();
				$("body").waitMe({
					effect: current_effect,
					text: text,
					bg: "rgba(255,255,255,0.7)",
					color: "#000",
					maxSize: "",
					waitTime: -1,
					source: "images/img.svg",
					textPos: "vertical",
					fontSize: "",
					onClose: function(el) {}
				});
				var url = app_base_url+'home/orderDetails/'+offset+'/'+limit+'/'+search_val;
				window.location.href = url;
			}
		});
		$('body').on('keypress','#searchVal',function(e){
			if(e.which == 13) {
				var search_val = $('body #searchVal').val();
				var offset = 1;
				$("body").waitMe({
					effect: current_effect,
					text: text,
					bg: "rgba(255,255,255,0.7)",
					color: "#000",
					maxSize: "",
					waitTime: -1,
					source: "images/img.svg",
					textPos: "vertical",
					fontSize: "",
					onClose: function(el) {}
				});
				var url = app_base_url+'home/orderDetails/'+offset+'/'+limit+'/'+search_val;
				window.location.href = url;
			}
		});
	</script>
  </body>
</html>
<?php
function pagination($pn, $totalPages)
                        {
                            $html = '<div class="text-end">';
                            $html .= '<ul class="pagination">';
                            if (($pn - 1) > 1) {
                                $html .= '<li><a class="ImagePagination" data-page="1" ><div class="page-a-link">1</div></a></li>';
                                $html .= '<li><a href="#" ><div class="page-before-after">...</div></a></li>';
                            }

                            for ($i = ($pn - 1); $i <= ($pn + 1); $i++) {
                                if ($i < 1) {
                                    continue;
                                }
                                if ($i > $totalPages) {
                                    break;
                                }
                                if ($i === $pn) {
                                    $class = 'active';
                                } else {
                                    $class = 'page-a-link';
                                }
                                $html .= '<li class="' . $class . '" ><a href="#" class="ImagePagination" data-page="' . $i . '" >';
                                $html .= '<div>' . $i . '</div>';
                                $html .= '</a></li>';
                            }
                            if (($totalPages - ($pn + 1)) >= 3) {
                                $html .= '<li><a href="#" ><div class="page-before-after">...</div></a></li>';
                            }
                            if (($totalPages - ($pn + 1)) > 0) {
                                if ($pn === $totalPages) {
                                    $class = 'active';
                                } else {
                                    $class = 'page-a-link';
                                }
                                $html .= '<li class="' . $class . '" ><a class="ImagePagination" data-page="' . $totalPages . '"><div >' . $totalPages . '</div></a></li>';
                            }
                            $html .= '</ul></div>';

                            return $html;
                        }
?>