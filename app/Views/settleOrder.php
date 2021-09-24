<?php include 'template/header.php'; ?>
    <main class="main-content">
      <div class="container">
        <div class="row">
          <div class="col-12 col-md-12 ">
            <div class="card">
                  <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-6">
                            <p>Settle Client Transaction</p>
                        </div>
                        <div class="col-md-6 text-end col-sm-6 col-6">
                            <a style="color:#FFF; font-size: 18px; text-decoration: none;" href="<?= getenv('app.baseURL') ?>"><p><i class="fas fa-arrow-left me-2"></i>Back to dashboard</p></a>
                        </div>
                    </div>
                  </div>
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
                        <div class="col-md-3 col-sm-12 col-12">
                            <p class="light-text-34">Amount Paid</p>
                            <p><strong><?= $orderDetails['currency'] . ' ' . $orderDetails['amount_paid'] ?></strong></p>
                        </div>
                         <div class="col-md-3 col-sm-12 col-12">
                            <p class="light-text-34">Settlement Status</p>
							<?php if ($orderDetails['settlement_status'] === 'COMPLETED') { ?>
							 <p><span class="badge bg-success"><?= ucfirst(strtolower($orderDetails['settlement_status'])) ?></span></p>
							<?php } else { ?>
                            <p><span class="badge btn-pink "><?= ucfirst(strtolower($orderDetails['settlement_status'])) ?></span></p>
							<?php } ?>
                        </div>
                    </div>
                  </div>
            </div>
			<?php if ($orderDetails['settlement_status'] !== 'COMPLETED') { ?>
			<form id="settleOrder" action="<?= getenv('app.baseURL') ?>settleOrder/proceedSettle" method="POST" >
            <div class="row mt-3">
                <div class="col-md-12 text-end">
					<input type="hidden" name="invoice_id" value="<?= $orderDetails['invoice_id'] ?>" />
                    <button type="submit" class="btn update">Capture</button>
                </div>
            </div>
			</form>
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
					$.toaster({ priority : "success", title : "Success", message : "Settlement Processed Successfully" });
				}else if(error == 1){
					$.toaster({ priority : "danger", title : "Error", message : "Settlement Process Failed" });
				}else if(error == 2){
					$.toaster({ priority : "danger", title : "Error", message : "Something Went Wrong" });
				}
			}
		});
		$('body').on('submit','#settleOrder',function(e){
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
