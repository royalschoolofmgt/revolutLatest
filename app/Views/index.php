	<?php include 'template/header.php'; ?>
    <main class="main-content retail-merchant">
		<div class="container">
			<div class="col-md-12 col-12 mx-auto">
				<div class="row">
					<div class="col-12 mb-5">
						<div class="card">
							<div class="card-header">
								<div class="row">
									<div class="col-6">
										<p>Information</p>
									</div>
								</div>
							</div>
							<div class="card-body">
								<div class="row p-3">
									<p>Welcome to the <b>Revolut Gateway for BigCommerce plugin!</b></p>
									<p>To start accepting payments from your customers at great rates, you'll need to follow three simple steps:</p>
									<ul style="list-style-type: disc; margin-left: 50px;">
										<li>
											<a href="https://business.revolut.com/signup" target="_blank">Sign up for Revolut Business</a> if you don't have an account already.
										</li>
										<li>
											Once your Revolut Business account has been approved, <a href="https://business.revolut.com/merchant" target="_blank">apply for a Merchant Account</a>
										</li>
										<li>
											<a href="https://business.revolut.com/settings/merchant-api" target="_blank">Get your Production API key</a> and paste it in the corresponding field below
										</li>
									</ul>
									<p>
										<a href="https://www.revolut.com/business/online-payments" target="_blank">Find out more</a> about why accepting payments through Revolut is the right decision for your business.
									</p>
									<p>
										If you'd like to know more about how to configure this plugin for your needs, <a href="https://developer.revolut.com/docs/accept-payments/plugins/bigcommerce/configuration" target="_blank">check out our documentation.</a>
									</p>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<div class="row">
									<div class="col-6">
										<p>Settings</p>
									</div>
								</div>
							</div>
							<div class="card-body">
								<div class="row p-3">
									<?php
                                        $payment_option = 'CFO';
                                        $enabled        = false;
                                        if ($clientDetails['is_enable'] === "1") {
                                            $enabled = true;
                                        }
                                        if (isset($clientDetails['payment_option'])) {
                                            $payment_option = $clientDetails['payment_option'];
                                        }
                                        $is_test_live = $clientDetails['is_test_live'];
                                        if ($is_test_live === '0') {
                                            $key = $clientDetails['revolut_api_key_test'];
                                        } else {
                                            $key = $clientDetails['revolut_api_key'];
                                        }
                                    ?>
									<div class="col-md-4 col-sm-6 col-12 my-3">
										<div class="d-flex switch-group mb-3">
											<div class="btn-group btn-toggle"  role="group">
												<button class="btn btn-sm <?= ($is_test_live === '1') ? '' : 'active' ?>"  style="width: 100px;" type="button" >SANDBOX</button>
												<button class="btn btn-sm <?= ($is_test_live === '0') ? 'btn-primary' : ' active' ?>" type="button"  style="width: 100px;" role="button">LIVE</button>
												<input class="form-check-input" name="is_test_live" type="hidden" id="is_test_live" value="<?= $is_test_live ?>" >
											</div>
										</div>
										<div class="mb-3" id="testAPIDiv" style="<?= ($is_test_live === '1') ? 'display:none;' : '' ?>" >
											<p class="text-col">Sandbox API key</p>
											<p ><input type="password" class="form-control" id="test_api_key" placeholder="Enter Sandbox API Key" value="<?= $clientDetails['revolut_api_key_test'] ?>" /></p>
										</div>
										<div class="mb-3" id="liveAPIDiv" style="<?= ($is_test_live === '0') ? 'display:none;' : '' ?>" >
											<p class="text-col">Live API key</p>
											<p ><input type="password" class="form-control" id="live_api_key" placeholder="Enter Live API Key" value="<?= $clientDetails['revolut_api_key'] ?>" /></p>
										</div>
									</div>
									<div class="col-md-4 col-sm-6 col-12 my-3">
										<p class="text-col">Payment Options</p>
										<div class="form-check">
										  <input class="form-check-input" type="radio" name="payment_option" <?= ($payment_option === 'CFO') ? 'checked' : '' ?> value="CFO" id="flexRadioDefault1" >
										  <label class="form-check-label" for="flexRadioDefault1">
											Capture on order placed
										  </label>
										</div>
										<div class="form-check">
											<input class="form-check-input" type="radio" name="payment_option" <?= ($payment_option === 'CFS') ? 'checked' : '' ?> value="CFS" id="flexRadioDefault2">
											<label class="form-check-label" for="flexRadioDefault2">
												Capture on Shipment
											</label>
										</div>
									</div>
									<div class="col-sm-2 col-md-2 col-12 my-3">
										<p class="text-col">Enable Revolut Plugin</p>
										<div class="form-check form-switch">
											<input class="form-check-input" type="checkbox" id="actionChange" <?= ($enabled) ? 'checked' : '' ?> value="<?= ($enabled) ? '1' : '0' ?>" >
										</div>
									</div>
								</div>
								<div class="row" id="saveChangesButton" style="display:none;">
									<div class="col-md-12 text-end">
										<button type="button" id="switchButton" class="btn update">Save Changes</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row mt-2  mb-4">
					<div class="col-md-10  section-update text-start">
					 <p >Support Email : <a href="mailto:support.bigcommerce@revolut.com">support.bigcommerce@revolut.com</a></p>
				</div>
				<!-- <div class="col-md-2 text-end">
					<button type="submit" class="btn update">Update</button>
				</div> -->
			</div>
			<div class="row mb-2">
				<div class="col-md-6 col-sm-6 col-5">
				   <h5>Order Details &nbsp;<img src="<?= getenv('app.ASSETSPATH') ?>images/refresh.svg" id="refreshButton" style="height:3%;width:3%;margin-top: -8px;">  &nbsp;<a href="<?= getenv('app.baseURL') . 'home/orderDetails' ?>"><button class="btn update" type="button">View all orders</button></a>
				</h5>
				</div>
			</div>
			<div class="table-responsive">
				<table id="myTable" class="table">
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

				</table>
			</div>
		</div>
    </main>
	<!-- Modal -->
	<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
				  <h5 class="modal-title" id="exampleModalLongTitle"><span><img src="<?= getenv('app.ASSETSPATH') ?>images/icons/trash-purple.svg" style="margin-top: -5px;"></span> <span class="purple">Remove Revolut Payments from Checkout</span>  </h5>
				  <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
				</div>
				<div class="modal-body" id="modalContent">
				  Are you sure you want to disable Revolut Payments? </strong>
				</div>
				<div class="modal-footer">
				  <button type="button" class="btn btn-order" id="cancelConfirm" data-dismiss="modal">Cancel</button>
				  <button type="button" class="btn update" id="deleteConfirm">Disable</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="SwitchToggleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
				  <h5 class="modal-title" id="titleToggle"><span class="purple">Switch mode sanbox/live</span>  </h5>
				  <button type="button" id="close" data-dismiss="modal" aria-label="Close">x</button>
				</div>
				<div class="modal-body" id="modalContentToggle">
				  <strong>Are you sure you want to switch to sandbox mode? </strong>
				</div>
				<div class="modal-footer">
				  <button type="button" class="btn btn-order" id="closeToggle" data-dismiss="modal">Cancel</button>
				  <button type="button" class="btn update" id="saveToggle">Save</button>
				</div>
			</div>
		</div>
	</div>
    <?php include 'template/footer.php'; ?>
	<script type="text/javascript">
		var text = "Please wait...";
		var current_effect = "bounce";
		var app_base_url = "<?= getenv('app.baseURL') ?>";
		var revolut_api_key_test = "<?= $clientDetails['revolut_api_key_test'] ?>";
		var revolut_api_key = "<?= $clientDetails['revolut_api_key'] ?>";
		$(document).ready(function() {
			$('body').on('click','#switchButton',function(){
				var is_test_live = $('body #is_test_live').val();
				if(is_test_live == "1"){
					var api_key = $('body #live_api_key').val();
				}else{
					var api_key = $('body #test_api_key').val();
				}
				if(api_key != ""){
					if(is_test_live == "1"){
						$('body #modalContentToggle').html('<strong>Are you sure you want to switch to Live mode? </strong>');
						$('body #titleToggle').html('<strong>Switch to Live mode </strong>');
					}else{
						$('body #modalContentToggle').html('<strong>Are you sure you want to switch to Sandbox mode? </strong>');
						$('body #titleToggle').html('<strong>Switch to Sandbox mode </strong>');
					}
					$('body #SwitchToggleModal').modal('show');
				}else{
					$.toaster({ priority : "danger", title : "Warning", message : "Please enter Api Key" });
				}
			});
			$('body').on('click','#closeToggle,#close',function(e){
				$('body #SwitchToggleModal').modal('hide');
			});
			$('body').on('click','#saveToggle',function(){
				var is_test_live = $('body #is_test_live').val();
				if(is_test_live == "1"){
					var api_key = $('body #live_api_key').val();
				}else{
					var api_key = $('body #test_api_key').val();
				}
				var payment_option = $('input[name=payment_option]:checked').val();
				if(api_key != ""){
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
					$.ajax({
						type: 'POST',
						url: app_base_url + "settings/switchToggle",
						dataType: 'json',
						data:{api_key:api_key,is_test_live:is_test_live,payment_option:payment_option},
						success: function (res) {
							if(res.status){
								var url = app_base_url+'home/index?switch=1';
								window.location.href = url;
							}else{
								$('body #SwitchToggleModal').modal('hide');
								$("body").waitMe("hide");
								$.toaster({ priority : "danger", title : "Error", message : res.msg });
							}
						}
					});

				}else{
					$.toaster({ priority : "danger", title : "Warning", message : "Please enter Api Key" });
				}
			});
			$('body').on('change','#actionChange',function(){
				if(revolut_api_key!= "" || revolut_api_key_test != ""){
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
					var val = $(this).val();
					if(val == "0"){
						var url = app_base_url+'settings/bcEnablePayment';
						window.location.href = url;
					}else{
						$('body #exampleModalCenter').modal('show');
						$("body").waitMe("hide");
					}
				}else{
					$.toaster({ priority : "danger", title : "Warning", message : "Please enter Api Key" });
					$('#actionChange').prop('checked',false);
				}
			});
			$('body').on('click','#deleteConfirm',function(e){
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
				var url = app_base_url+'settings/bcDisablePayment';
				window.location.href = url;
			});
			$('body').on('click','#cancelConfirm,.close',function(e){
				$('body #exampleModalCenter').modal('hide');
				$('#actionChange').trigger('click');
			});
		});
		var getUrlParameter = function getUrlParameter(sParam) {
			var sPageURL = window.location.search.substring(1),
				sURLVariables = sPageURL.split("&"),
				sParameterName,
				i;

			for (i = 0; i < sURLVariables.length; i++) {
				sParameterName = sURLVariables[i].split("=");

				if (sParameterName[0] === sParam) {
					return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
				}
			}
			return false;
		};
		$(document).ready(function(){
			var enabled = getUrlParameter('enabled');
			if(enabled){
				$.toaster({ priority : "success", title : "Success", message : "Revolut Payments enabled for your Store" });
			}
			var disabled = getUrlParameter('disabled');
			if(disabled){
				$.toaster({ priority : "success", title : "Success", message : "Revolut Payments disabled for your Store" });
			}
			var updated = getUrlParameter('updated');
			if(updated){
				$.toaster({ priority : "success", title : "Success", message : "Payment Option Updated. Please note that your changes will take a few minutes to be applied to your storefront." });
			}
			var switched = getUrlParameter('switch');
			if(switched){
				$.toaster({ priority : "success", title : "Success", message : "Please note that your changes will take a few minutes to be applied to your storefront." });
			}
		});
		$('body').on('submit','#updateSettings',function(e){
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
		$('body').on('click','#refreshButton',function(){
			$("body").waitMe({
				effect: current_effect,
				text: text,
				bg: "rgba(255,255,255,0.7)",
				color: "#000",
				maxSize: "",
				waitTime: -1,
				// source: "images/img.svg",
				textPos: "vertical",
				fontSize: "",
				onClose: function(el) {}
			});
			var url = app_base_url+'home/index';
			window.location.href = url;
		});
		$('.btn-toggle').click(function() {
			$(this).find('.btn').toggleClass('active');
			var val = $('body #is_test_live').val();
			if(val == "1"){
				$('body #is_test_live').val(0);
			}else{
				$('body #is_test_live').val(1);
			}

			if ($(this).find('.btn-info').length>0) {
			  $(this).find('.btn').toggleClass('btn-info');
			}

			$('body #testAPIDiv').toggle('show');
			$('body #liveAPIDiv').toggle('show');
			$('body #saveChangesButton').show();

		});
		$('body').on('change','input[name=payment_option],#test_api_key,#live_api_key',function(){
			$('body #saveChangesButton').show();
		});

	</script>
  </body>
</html>
