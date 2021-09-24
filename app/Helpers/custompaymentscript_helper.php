<?php

/**
 * This file is part of 247Commerce BigCommerce Revolut App.
 *
 * (c) 2021 247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * Class custompaymentscript_helper
 *
 * Represents a helper class to create Payment Script in BigCommerce
 */
class custompaymentscript_helper
{
    /**
     * createPaymentScript - to create custom script and saved in folder to inject scipt file in bigcommerce
     *
     * @param text| $sellerdb
     * @param text| $email_id
     * @param text| $validation_id
     * @param text| $is_test_live
     * @param text| $payment_option
     */
    public static function createPaymentScript($sellerdb, $email_id, $validation_id, $is_test_live, $payment_option = 'CFO')
    {
        $tokenData = ['email_id' => $email_id, 'key' => $validation_id];
        if (! empty($sellerdb)) {
            $enable       = 0;
            $payment_name = 'Pay With Card';
            $buttonCode   = '<button id="pay-button-hpp" type="submit" class="button button--action button--large button--slab optimizedCheckout-buttonPrimary" style="background-color: #424242;border-color: #424242;color: #fff;">Place Order</button>';

            $powered_bylogo = '<img style="margin-top: -30px;" src="' . getenv('app.ASSETSPATH') . 'images/dark.svg" />';
            $revolutPayButton = new \App\Models\CustomRevolutPayButtonModel();
            $condition        = [
                'email_id'            => $email_id,
                'token_validation_id' => $validation_id,
            ];
            $db_resp = $revolutPayButton->getData($condition);
            if ($db_resp['status']) {
                $result_cc      = $db_resp['data'];
                $payment_name   = $result_cc['payment_name'];
                if ($result_cc['powerby_logo'] === 'au') {
                    $powered_bylogo = '<img style="margin-top: -30px;" src="' . getenv('app.ASSETSPATH') . 'images/light.svg" />';
                }
                if (isset($result_cc['is_enabled']) && $result_cc['is_enabled'] === 1) {
                    $enable = 1;
                }
            }

            $FormCode = '<li class=\"form-checklist-item optimizedCheckout-form-checklist-item\"><div class=\"form-checklist-header\" style=\"background:#F9F9F9;\"><div class=\"form-field\"><input id=\"radio-revolutpay\" name=\"revolutPayments\" class=\"form-checklist-checkbox optimizedCheckout-form-checklist-checkbox\" type=\"radio\" value=\"revolutpay\" checked><label for=\"radio-revolutpay\" class=\"form-label optimizedCheckout-form-label\"><span class=\"paymentProviderHeader-name\" data-test=\"payment-method-name\"><span class=\"paymentProviderHeader-name\" data-test=\"payment-method-name\">' . $payment_name . '</span></label></div></div></li>';
            $FormCode .= '<form id="revolutPaymentForm" name="revolutPayment"><input type="hidden" id="247revolutkey" value="' . base64_encode(json_encode($tokenData)) . '" ><div id="revolut_hpp"></div>' . $powered_bylogo . '<div id="buttonCode">' . $buttonCode . '</div></form>';

            //to check test or live mode
            $widgetMode = 'sandbox';
            if ($is_test_live === '1') {
                $widgetMode = 'prod';
            }

            $folderPath  = getenv('app.SCRIPSPATH') . $sellerdb;
            $filecontent = '!function(e,o,t){e[t]=function(n,r){var c={sandbox:"https://sandbox-merchant.revolut.com/embed.js",prod:"https://merchant.revolut.com/embed.js",dev:"https://merchant.revolut.codes/embed.js"},d=o.createElement("script");d.id="revolut-checkout",d.src=c[r]||c.prod,d.async=!0,o.head.appendChild(d);var s={then:function(r,c){d.onload=function(){r(e[t](n))},d.onerror=function(){o.head.removeChild(d),c&&c(new Error(t+" is failed toload"))}}};return"function"==typeof Promise?Promise.resolve(s):s}}(window,document,"RevolutCheckout");';
            $filecontent .= '$("head").append("<script src=\"' . getenv('app.ASSETSPATH') . 'js/247revolutloader.js\" ></script>");';
            $filecontent .= '$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"' . getenv('app.ASSETSPATH') . 'css/247revolutloader.css\" />");';
            $filecontent .= '$("head").append("<link rel=\"stylesheet\" type=\"text/css\" href=\"' . getenv('app.ASSETSPATH') . 'css/hostedfields.css\" />");';
            $textcolor = '#666';
            if ($db_resp['status']) {
                $result_c = $db_resp['data'];
                $id       = $result_c['container_id'];

                if (isset($result_cc['is_enabled']) && $result_cc['is_enabled'] === 1) {
                    $css_prop = $result_c['css_prop'];
                } else {
                    $textcolor = $result_c['textcolor'];
                    $css_prop  = '#revolut_hpp{
	background-color: ' . $result_c['buttoncolor'] . ' !important;
	color: ' . $result_c['textcolor'] . ' !important;
	border-color: ' . $result_c['outlinecolor'] . ' !important;
}';
                }
                if (! empty($id)) {
                    $filecontent .= 'var payment_option = "' . $payment_option . '";$(document).ready(function() {
						var stIntIdPayu = setInterval(function() {
							if($(".checkout-step--payment").length > 0) {
								if($("#247revolutpayment").length == 0){
									$("' . $id . '").after(\'<div id="247revolutpayment" class="checkout-form" style="padding:1px;display:none;margin-top: 15px;"><div id="247revolutErr" style="color:red"></div>' . $FormCode . '</div>\');
									loadRevolutStatus();
									clearInterval(stIntIdPayu);
									/**
										when user is logged in and billing/shipping
										address set show custom payment button
									*/
									checkRevolutPayBtnVisibility();
								}
							}
						}, 1000);';
                } else {
                    $filecontent .= 'var payment_option = "' . $payment_option . '";$(document).ready(function() {
						var stIntIdPayu = setInterval(function() {
							if($(".checkout-step--payment").length > 0) {
								if($("#247revolutpayment").length == 0){
									$(".checkout-step--payment .checkout-view-header").after(\'<div id="247revolutpayment" class="checkout-form" style="padding:1px;display:none;margin-top: 15px;"><div id="247revolutErr" style="color:red"></div>' . $FormCode . '</div>\');
									loadRevolutStatus();
									clearInterval(stIntIdPayu);
									/**
										when user is logged in and billing/shipping
										address set show custom payment button
									*/
									checkRevolutPayBtnVisibility();
								}
							}
						}, 1000);';
                }
                if (! empty($css_prop)) {
                    $filecontent .= '$("body").append("<style>' . preg_replace("/[\r\n]*/", '', $css_prop) . '</style>");';
                }
            } else {
                $css_prop = '#revolut_hpp{
	background-color: #ffff !important;
	color: 3fff !important;
	border-color: #424242 !important;
}';
                $filecontent .= '$("body").append("<style>' . preg_replace("/[\r\n]*/", '', $css_prop) . '</style>");';
                $filecontent .= 'var payment_option = "' . $payment_option . '";$(document).ready(function() {
						var stIntIdPayu = setInterval(function() {
							if($(".checkout-step--payment").length > 0) {
								if($("#247revolutpayment").length == 0){
									$(".checkout-step--payment .checkout-view-header").after(\'<div id="247revolutpayment" class="checkout-form" style="padding:1px;display:none;margin-top: 15px;"><div id="247revolutErr" style="color:red"></div>' . $FormCode . '</div>\');
									loadRevolutStatus();
									clearInterval(stIntIdPayu);
									/**
										when user is logged in and billing/shipping
										address set show custom payment button
									*/
									checkRevolutPayBtnVisibility();
								}
							}
						}, 1000);';
            }
            $filecontent .= '$("body").on("click","button[data-test=\'step-edit-button\'], button[data-test=\'sign-out-link\']",function(e){
					//hide revolut payment button
					$("#247revolutpayment").hide();
				});

				$("body").on("click", "button#checkout-customer-continue, button#checkout-shipping-continue, button#checkout-billing-continue", function() {
					setTimeout(checkRevolutPayBtnVisibility, 2000);
				});
				$("body").on("click", "#useStoreCredit", function() {
					setTimeout(checkRevolutPayBtnVisibility, 2000);
				});
				$("body").on("click", "#applyRedeemableButton", function() {
					setTimeout(checkRevolutPayBtnVisibility, 2000);
				});
				$("body").on("click", ".cart-priceItem-link", function() {
					setTimeout(checkRevolutPayBtnVisibility, 2000);
				});
			});
			function revolutbillingAddressValdation(billingAddress){
				var errorCount = 0;
				if(typeof(billingAddress.firstName) != "undefined" && billingAddress.firstName !== null && billingAddress.firstName !== "") {

				}else{
					errorCount++;
				}
				if(typeof(billingAddress.lastName) != "undefined" && billingAddress.lastName !== null && billingAddress.lastName !== "") {

				}else{
					errorCount++;
				}
				if(typeof(billingAddress.address1) != "undefined" && billingAddress.address1 !== null && billingAddress.address1 !== "") {

				}else{
					errorCount++;
				}
				if(typeof(billingAddress.email) != "undefined" && billingAddress.email !== null && billingAddress.email !== "") {

				}else{
					errorCount++;
				}
				if(typeof(billingAddress.city) != "undefined" && billingAddress.city !== null && billingAddress.city !== "") {

				}else{
					errorCount++;
				}
				if(typeof(billingAddress.postalCode) != "undefined" && billingAddress.postalCode !== null && billingAddress.postalCode !== "") {

				}else{
					errorCount++;
				}
				if(typeof(billingAddress.country) != "undefined" && billingAddress.country !== null && billingAddress.country !== "") {

				}else{
					errorCount++;
				}

				return errorCount;
			}

			function revolutshippingAddressValdation(shippingAddress){
				var errorCount = 0;
				if(shippingAddress.length > 0){
					if(typeof(shippingAddress[0].shippingAddress) != "undefined" && shippingAddress[0].shippingAddress !== null && shippingAddress[0].shippingAddress !== "") {
						shippingAddress = shippingAddress[0].shippingAddress;
						if(typeof(shippingAddress.firstName) != "undefined" && shippingAddress.firstName !== null && shippingAddress.firstName !== "") {

						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.lastName) != "undefined" && shippingAddress.lastName !== null && shippingAddress.lastName !== "") {

						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.address1) != "undefined" && shippingAddress.address1 !== null && shippingAddress.address1 !== "") {

						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.city) != "undefined" && shippingAddress.city !== null && shippingAddress.city !== "") {

						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.postalCode) != "undefined" && shippingAddress.postalCode !== null && shippingAddress.postalCode !== "") {

						}else{
							errorCount++;
						}
						if(typeof(shippingAddress.country) != "undefined" && shippingAddress.country !== null && shippingAddress.country !== "") {

						}else{
							errorCount++;
						}
					}
				}else{
					errorCount++;
				}
				return errorCount;
			}
			function checkOnlyDownloadableProducts(cartData){
				var status = false;
				if(cartData != ""){
					if(cartData.physicalItems.length > 0 || cartData.customItems.length > 0){
						status = true;
					}
					else{
						if(cartData.digitalItems.length > 0){
							status = false;
						}
					}
				}
				return status;
			}
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
			function loadRevolutStatus(){
				var key = getUrlParameter("revolutinv");
				if(key != "undefined" && key != ""){
					$.ajax({
						type: "POST",
						dataType: "json",
						crossDomain: true,
						url: "' . getenv('app.baseURL') . 'revolutPay/getPaymentStatus",
						dataType: "json",
						data:{"authKey":key},
						success: function (res) {
							if(res.status){
								$("body #247revolutErr").text(res.msg);
							}
						}
					});
				}
			}
			';
            $filecontent .= 'function checkRevolutPayBtnVisibility() {
				var checkDownlProd = false;
				var key = $("body #247revolutkey").val();
				var text = "Please wait...";
				var current_effect = "bounce";
				$("#247revolutpayment").waitMe({
					effect: current_effect,
					text: text,
					bg: "rgba(255,255,255,0.7)",
					color: "#000",
					maxSize: "",
					waitTime: -1,
					source: "' . getenv('app.ASSETSPATH') . 'images/img.svg",
					textPos: "vertical",
					fontSize: "",
					onClose: function(el) {}
				});
				$.ajax({
					type: "GET",
					dataType: "json",
					url: "/api/storefront/cart",
					success: function (res) {
						if(res.length > 0){
							if(res[0]["id"] != undefined){
								var cartId = res[0]["id"];
								var cartCheck = res[0]["lineItems"];
								checkDownlProd = checkOnlyDownloadableProducts(cartCheck);
								var text = "Please wait...";
								var current_effect = "bounce";
								if(cartId != ""){
									$.ajax({
										type: "GET",
										dataType: "json",
										url: "/api/storefront/checkouts/"+cartId+"?include=cart.lineItems.physicalItems.options%2Ccart.lineItems.digitalItems.options%2Ccustomer%2Ccustomer.customerGroup%2Cpayments%2Cpromotions.banners%2Ccart.lineItems.physicalItems.categoryNames%2Ccart.lineItems.digitalItems.categoryNames",
										success: function (cartres) {
											var cartData = window.btoa(unescape(encodeURIComponent(JSON.stringify(cartres))));
											var billingAddress = "";
											var consignments = "";
											var bstatus = 0;
											var sstatus = 0;
											if(typeof(cartres.billingAddress) != "undefined" && cartres.billingAddress !== null) {
												billingAddress = cartres.billingAddress;
												bstatus = revolutbillingAddressValdation(billingAddress);
											}
											if(checkDownlProd){
												if(typeof(cartres.consignments) != "undefined" && cartres.consignments !== null) {
													consignments = cartres.consignments;
													sstatus = revolutshippingAddressValdation(consignments);
												}
											}
											var grandTotal = parseFloat(cartres.grandTotal);
											var StoreCreditAmount = 0;
											var totalCartPrice = 0;
											var isStoreCreditApplied = cartres.isStoreCreditApplied;
											if(parseInt(cartres.cart["customerId"]) > 0){
												if(isStoreCreditApplied == true){
													StoreCreditAmount = parseFloat(cartres.customer["storeCredit"]);
												}
											}
											if(StoreCreditAmount > 0){
												if(grandTotal > StoreCreditAmount){
													totalCartPrice = parseFloat(parseFloat(grandTotal)-parseFloat(StoreCreditAmount));
												}
											}else{
												totalCartPrice = grandTotal;
											}
											if(bstatus ==0 && sstatus == 0 && parseFloat(totalCartPrice)>0) {

												$.ajax({
													type: "POST",
													dataType: "json",
													crossDomain: true,
													url: "' . getenv('app.baseURL') . 'revolutPay/authentication",
													dataType: "json",
													data:{"authKey":key,"cartId":cartId,isStoreCreditApplied:isStoreCreditApplied},
													success: function (res) {
														$("#247revolutpayment").waitMe("hide");
														window.revolutApiRes = res;
														if(RevolutCheckout && res && res.rev_public_id && res.status) {
															payBtnControl("radio-revolutpay", "pay-button-hpp", "revolutPaymentForm");
															$("#247revolutpayment").show();
															RevolutCheckout(res.rev_public_id, "' . $widgetMode . '").then(function (RC) {

																var payButtonHPP = document.getElementById("pay-button-hpp");
																payButtonHPP.addEventListener("click", function () {
																	console.log("clickedhppButton");
																	$("#247revolutpayment").waitMe({
																		effect: current_effect,
																		text: text,
																		bg: "rgba(255,255,255,0.7)",
																		color: "#000",
																		maxSize: "",
																		waitTime: -1,
																		source: "' . getenv('app.ASSETSPATH') . 'images/img.svg",
																		textPos: "vertical",
																		fontSize: "",
																		onClose: function(el) {}
																	});
																	var shipAddress = consignments[0].shippingAddress;
																	if(shipAddress) {
																		instance.submit({
																			// (mandatory!) name of the cardholder
																			name: billingAddress.firstName+" "+billingAddress.lastName,
																			// (optional) email of the customer
																			email: billingAddress.email,
																			// (optional) phone of the customer
																			phone: billingAddress.phone,
																			// (optional) billing address of the customer
																			billingAddress: {
																			  countryCode: billingAddress.countryCode, //if sending billing address, this field is mandatory
																			  region: billingAddress.stateOrProvince,
																			  city: billingAddress.city,
																			  streetLine1: billingAddress.address1,
																			  streetLine2: billingAddress.address2,
																			  postcode: billingAddress.postalCode //if sending billing address, this field is mandatory
																			},
																			// (optional) shipping address of the customer
																			shippingAddress: {
																			  countryCode: shipAddress.countryCode, //if sending shipping address, this field is mandatory
																			  region: shipAddress.stateOrProvince,
																			  city: shipAddress.city,
																			  streetLine1: shipAddress.address1,
																			  streetLine2: shipAddress.address2,
																			  postcode: shipAddress.postalCode //if sending shipping address, this field is mandatory
																			}

																		});
																	}
																	else {
																		instance.submit({
																			// (mandatory!) name of the cardholder
																			name: billingAddress.firstName+" "+billingAddress.lastName,
																			// (optional) email of the customer
																			email: billingAddress.email,
																			// (optional) phone of the customer
																			phone: billingAddress.phone,
																			// (optional) billing address of the customer
																			billingAddress: {
																			  countryCode: billingAddress.country_code, //if sending billing address, this field is mandatory
																			  region: billingAddress.state_or_province,
																			  city: billingAddress.city,
																			  streetLine1: billingAddress.address1,
																			  streetLine2: billingAddress.address2,
																			  postcode: billingAddress.postalCode //if sending billing address, this field is mandatory
																			}

																		});
																	}

																});
																var instance = RC.createCardField({
																	target: document.getElementById("revolut_hpp"),
																	hidePostcodeField: true,
																	styles: {
																	  default: {
																		color: "#000",
																		"::placeholder": {
																		  color: "' . $textcolor . '"
																		}
																	  },
																	  autofilled: {
																		color: "#000"
																	  }
																	},
																	onSubmit(res) {
																		console.log("submitted");
																	},
																	onValidation(errors) {
																		console.log(errors);
																		console.log(errors.length,"length");
																		////alert(errors[0].message);
																		$("#247revolutErr").html(errors.join(" <br> "));
																		if(parseInt(errors.length) > 0){
																			$("#247revolutpayment").waitMe("hide");
																		}
																	},
																	onSuccess() {
																		//$("#247revolutpayment").waitMe("hide");
																		window.location.href = "' . getenv('app.baseURL') . 'revolutPay/payment/"+window.revolutApiRes.invoiceId+"/"+window.revolutApiRes.rev_order_id;
																	},
																	onError(error) {
																	  console.log(error);
																	  ////alert(errors[0].message);
																	  $("#247revolutErr").html(error);
																	  $("#247revolutpayment").waitMe("hide");
																	},
																	onCancel() {
																	  console.log(error);
																	  $("#247revolutErr").html(error);
																	  $("#247revolutpayment").waitMe("hide");
																	}
																  });

															});
														}
														else {
															$("#247revolutpayment").waitMe("hide");
															$("#247revolutErr").html(res.msg);
														}

													},error: function(err){
														$("#247revolutpayment").waitMe("hide");
													}
												});
											}else if(bstatus ==0 && sstatus == 0) {
												//show default place order button to directly create bigcommerce order for cart amount 0 without processing payment gateway
												//assuming default bigcommer place order button id: checkout-payment-continue
												if(!$("#checkout-payment-continue").length) {
													$("#247revolutpayment").waitMe("hide");
													$(".optimizedCheckout-form-checklist-item").hide();
													$("#revolut_hpp").hide();
													//display a default place order button
													$("#buttonCode").html(\'<div id="247revolutMsg" style="padding-bottom:15px;">Payment is not required for this order.</div><button id="custom_place_order_btn" class="button button--action button--large button--slab optimizedCheckout-buttonPrimary" type="button">Place Order</button>\');
													$("#247revolutpayment").show();
												}
											}
										}
									});
								}
							}
						}
					}

				});
			}
			$("body").on("submit","#revolutPaymentForm",function(e){
				e.preventDefault();
				var text = "Please wait...";
				var current_effect = "bounce";
				var key = $("body #247revolutkey").val();
				$("#247revolutpayment").waitMe({
					effect: current_effect,
					text: text,
					bg: "rgba(255,255,255,0.7)",
					color: "#000",
					maxSize: "",
					waitTime: -1,
					source: "' . getenv('app.ASSETSPATH') . 'images/img.svg",
					textPos: "vertical",
					fontSize: "",
					onClose: function(el) {}
				});
			});
			$("body").on("click","#custom_place_order_btn",function(e){
				e.preventDefault();
				var text = "Please wait...";
				var current_effect = "bounce";
				var key = $("body #247revolutkey").val();
				$("#247revolutpayment").waitMe({
					effect: current_effect,
					text: text,
					bg: "rgba(255,255,255,0.7)",
					color: "#000",
					maxSize: "",
					waitTime: -1,
					source: "' . getenv('app.ASSETSPATH') . 'images/img.svg",
					textPos: "vertical",
					fontSize: "",
					onClose: function(el) {}
				});
				$.ajax({
					type: "GET",
					dataType: "json",
					url: "/api/storefront/cart",
					success: function (res) {
						if(res.length > 0){
							if(res[0]["id"] != undefined){
								var cartId = res[0]["id"];
								var cartCheck = res[0]["lineItems"];
								checkDownlProd = checkOnlyDownloadableProducts(cartCheck);
								if(cartId != ""){
									$.ajax({
										type: "GET",
										dataType: "json",
										url: "/api/storefront/checkouts/"+cartId+"?include=cart.lineItems.physicalItems.options%2Ccart.lineItems.digitalItems.options%2Ccustomer%2Ccustomer.customerGroup%2Cpayments%2Cpromotions.banners%2Ccart.lineItems.physicalItems.categoryNames%2Ccart.lineItems.digitalItems.categoryNames",
										success: function (cartres) {
											var cartData = window.btoa(unescape(encodeURIComponent(JSON.stringify(cartres))));
											var billingAddress = "";
											var consignments = "";
											var bstatus = 0;
											var sstatus = 0;
											if(typeof(cartres.billingAddress) != "undefined" && cartres.billingAddress !== null) {
												billingAddress = cartres.billingAddress;
												bstatus = revolutbillingAddressValdation(billingAddress);
											}
											if(checkDownlProd){
												if(typeof(cartres.consignments) != "undefined" && cartres.consignments !== null) {
													consignments = cartres.consignments;
													sstatus = revolutshippingAddressValdation(consignments);
												}
											}
											var grandTotal = parseFloat(cartres.grandTotal);
											var StoreCreditAmount = 0;
											var totalCartPrice = 0;
											var isStoreCreditApplied = cartres.isStoreCreditApplied;
											if(parseInt(cartres.cart["customerId"]) > 0){
												if(isStoreCreditApplied == true){
													StoreCreditAmount = parseFloat(cartres.customer["storeCredit"]);
												}
											}
											if(StoreCreditAmount > 0){
												if(grandTotal > StoreCreditAmount){
													totalCartPrice = parseFloat(parseFloat(grandTotal)-parseFloat(StoreCreditAmount));
												}
											}else{
												totalCartPrice = grandTotal;
											}
											if(bstatus ==0 && sstatus == 0 && parseFloat(totalCartPrice)==0) {
												$.ajax({
													type: "POST",
													dataType: "json",
													crossDomain: true,
													url: "' . getenv('app.baseURL') . 'revolutPay/zauthentication",
													dataType: "json",
													data:{"authKey":key,"cartId":cartId,isStoreCreditApplied:isStoreCreditApplied},
													success: function (res) {
														if(res.status){
															window.location.href=res.url;
														}else{
															$("#247revolutpayment").show();
															alert("Technical error.");
														}
													}
												});
											}else{
												$("#247revolutpayment").show();
												alert("Please enter valid billing and shipping address");
											}
										}
									});
								}
							}
						}
					}
				});
			});';
            $filecontent .= 'function payBtnControl(custRadID, custPayBtnID, custFormID) {
				var payRevBtnIntId = setInterval(function() {
					if($(".checkout-step--payment").length > 0) {
						if($("#checkout-payment-continue").length > 0 && $(".loadingNotification").length == 0 && $("#"+custRadID).length > 0 && $("#"+custRadID).prop("checked")) {
							
							$(\'input[name="paymentProviderRadio"]\').each(function(i) {
								$(this).prop("checked", false);
							});

							$(".form-checklist-body").hide(100);
							$("#checkout-payment-continue").attr("disabled", "disabled");
							$("#checkout-payment-continue").hide();
							clearInterval(payRevBtnIntId);
							
							//attach click event with custom radio btn & default radio btn container
							$("#"+custRadID).on("click", function() {
								
								//unchecked default radio button
								$(\'input[name="paymentProviderRadio"]\').each(function(i) {
									$(this).prop("checked", false);
								});

								//disable default place order button
								$(".form-checklist-body").hide(500);
								$("#checkout-payment-continue").attr("disabled", "disabled");	
								$("#checkout-payment-continue").hide();				

								//enable custom place order button
								$("#"+custFormID).show(500);
								$("#"+custPayBtnID).removeAttr("disabled");
								$("#"+custPayBtnID).css("opacity", "1");

							});

							$(\'input[name="paymentProviderRadio"]\').on("click", function() {
								
								//uncheck custom radio button
								$("#"+custRadID).prop("checked", false);

								//disable custom place order button
								$("#"+custFormID).hide(500);
								$("#"+custPayBtnID).attr("disabled", "disabled");
								$("#"+custPayBtnID).css("opacity", ".5");

								//enable default place order button
								$(".form-checklist-body").show(500);
								$("#checkout-payment-continue").removeAttr("disabled");
								$("#checkout-payment-continue").show();
							});


						}
					}
				}, 1000);

			}';
            $filename = 'custom_script.js';
            helper('filestream');
            \filestream_helper::saveFile($filename, $filecontent, $folderPath);
        }
    }
}
