<?php include 'template/header.php'; ?>
    <main class="main-content">
      <div class="container">
        <div class="row">
          <div class="col-12 col-md-12">
            <form id="proceedRefund" action="<?= getenv('app.baseURL') ?>refundOrder/proceedRefund" method="POST" >
			<div class="card">
                  <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-6">
                            <p >Refund Client Transaction</p>
                        </div>
                        <div class="col-md-6 text-end col-sm-6 col-6">
                            <a style="color:#FFF; font-size: 18px; text-decoration: none;" href="<?= getenv('app.baseURL') ?>"><p><i class="fas fa-arrow-left me-2"></i>Back to dashboard</p></a>
                        </div>
                    </div>
                  </div>
					<?php
                        $refunded_amount = 0;
                        $total_amount    = $orderDetails['total_amount'];
                        if (count($ref_result) > 0) {
                            foreach ($ref_result as $k => $v) {
                                if ($v['refund_status'] === 'COMPLETED') {
                                    $refunded_amount += $v['refund_amount'];
                                }
                            }
                        }
                    ?>
                  <div class="card-body">
                    <div class="row card-content p-3">
                        <div class="col-md-3 col-sm-3 px-md-2 col-6">
                            <p class="light-text">Email Id</p>
                            <p>
								<strong>
									<?php
                                        $params = json_decode(base64_decode($orderDetails['params'], true), true);
                                        if (isset($params['billing_address'])) {
                                            $billingAddress = $params['billing_address'];
                                            echo $billingAddress['email'];
                                        } else {
                                            echo $clientDetails['name'];
                                        }
                                    ?>
								</strong>
							</p>
                        </div>
                        <div class="col-md-3 col-sm-3 px-lg-4 col-6">
                            <p class="light-text">Order Id</p>
                            <p>
								<strong>
									<?php
                                        $api_response = json_decode(stripslashes($orderDetails['api_response']), true);
                                        if (isset($api_response['id'])) {
                                            echo $api_response['id'];
                                        } else {
                                            echo $orderDetails['invoice_id'];
                                        }
                                    ?>
								</strong>
							</p>
                        </div>
                        <div class="col-md-3 col-sm-3 px-lg-5 col-6">
                            <p class="light-text">Amount</p>
                            <p><strong><?= $orderDetails['currency'] . ' ' . $orderDetails['total_amount'] ?></strong></p>
                        </div>
                        <div class="col-md-3 col-sm-3 px-lg-5 col-6">
                            <p class="light-text">Status</p>
                            <p><span class="badge bg-success"><?= ucfirst(strtolower($orderDetails['status'])) ?></span></p>
                        </div>
                    </div>
                    <div class="row p-3">
						<?php if (($total_amount - $refunded_amount) > 0) { ?>
                        <div class="col-md-6 col-sm-12 col-12">
                            <span class="light-text-34">Enter Amount To Be Refunded</span>
							<input type="hidden" name="invoice_id" value="<?= $orderDetails['invoice_id'] ?>" />
                            <span class="badge"><p><input class="form-control bg-secondary border-0" type="number" required name="refund_amount" step="any" value="" min=0.1 max="<?= $orderDetails['total_amount'] - $refunded_amount ?>" /></p></span>
                        </div>
						<?php } ?>
                        <div class="col-md-6 see-tranc col-sm-12 col-12">
                            <span class="light-text-34">Amount Refunded Already</span>
                            <strong><?= $orderDetails['currency'] . ' ' . $refunded_amount ?></strong>
                            <a href="#" class="showTrans" >See Transactions</a>
                        </div>
                    </div>
                  </div>
            </div>
			<div class="col-md-12 s-conetnt collapse" id="demoTrans">
				<?php
                    if (count($ref_result) > 0) {
                        ?>
				<table class="table">
					<tr>
						<th>Refund Amount</th>
						<th>Refund Status</th>
						<th>Created Date</th>
					<tr>
					<?php
                        foreach ($ref_result as $rk => $rv) {
                            ?>
					<tr>
						<td><?= $rv['refund_amount'] ?></td>
						<td><?= $rv['refund_status'] ?></td>
						<td><?= date('d-m-Y h:i A', strtotime($rv['created_at'])) ?></td>
					</tr>
					<?php
                        } ?>
				</table>
				<?php
                    } else {
                        echo 'No Data Found';
                    }
                ?>
			</div>
			<?php if (($total_amount - $refunded_amount) > 0) { ?>
            <div class="row mt-3">
                <div class="col-md-12 text-end">
                    <button type="submit" class="btn update">Refund</button>
                </div>
            </div>
			<?php } ?>
          </div>
        </div>
      </div>
    </main>
  </body>
  <?php include 'template/footer.php'; ?>
	<script>
		var text = "Please wait...";
		var current_effect = "bounce";
		var getUrlParameter = function getUrlParameter(sParam) {
			var sPageURL = window.location.search.substring(1),
				sURLVariables = sPageURL.split('&'),
				sParameterName,
				i;

			for (i = 0; i < sURLVariables.length; i++) {
				sParameterName = sURLVariables[i].split('=');

				if (sParameterName[0] === sParam) {
					return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
				}
			}
			return false;
		};
		$(document).ready(function(){
			var error = getUrlParameter('error');
			if(error){
				if(error == 0){
					$.toaster({ priority : "success", title : "Success", message : "Refund Processed Successfully" });
				}else if(error == 1){
					$.toaster({ priority : "danger", title : "Error", message : "Refund Process Failed" });
				}else if(error == 2){
					$.toaster({ priority : "danger", title : "Error", message : "Something Went Wrong" });
				}
			}
		});
		$('body').on('click','.showTrans',function(e){
			e.preventDefault();
			if($('body #demoTrans').hasClass("collapse")){
				$('body #demoTrans').removeClass("collapse");
			}else{
				$('body #demoTrans').addClass("collapse");
			}
		});
		$('body').on('submit','#proceedRefund',function(e){
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
		});
		</script>
</html>
