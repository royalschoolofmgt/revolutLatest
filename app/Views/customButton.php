	<?php include 'template/header.php'; ?>
    <main class="main-content">
      <div class="container">
        <div class="row">
          <div class="col-12 col-md-12">
			<form action="<?= getenv('app.baseURL') ?>settings/updateCustomButton" id="updateCustomButton" method="POST" >
            <div class="card">
                  <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-6">
                            <p>Customise Payment Button</p>
                        </div>
                        <div class="col-md-6 text-end col-sm-6 col-6">
                            <p><a href="<?= getenv('app.baseURL') ?>" style="color:#ffff; font-size:18px;" ><i class="fas fa-arrow-left me-2"></i>Back to dashboard</a></p>
                        </div>
                    </div>
                  </div>
				  <?php
                        $container_id = '.checkout-step--payment .checkout-view-header';
                        $css_prop     = '#revolut_hpp{
	background-color: #00FF00 !important;
	color: #000000 !important;
	border-color: #FF0000 !important;
}';
$buttoncolor                          = '#000000';
$textcolor                            = '#FFFFFF';
$outlinecolor                         = '#FF00FF';
                        $result_c     = $buttonDetails;
                        $payment_name = 'Pay With Card';
                        if (isset($buttonDetails['id'])) {
                            $result_c = $buttonDetails;
                        } else {
                            $result_c['container_id'] = $container_id;
                            $result_c['css_prop']     = $css_prop;
                            $result_c['payment_name'] = $payment_name;
                            $result_c['buttoncolor']  = $buttoncolor;
                            $result_c['textcolor']    = $textcolor;
                            $result_c['outlinecolor'] = $outlinecolor;
                        }
                        //print_r($result_c);exit;
                        $enable = '';
                        if (isset($result_c['is_enabled']) && $result_c['is_enabled'] === '1') {
                            $enable = 'checked';
                        }
                    ?>
                  <div class="card-body">
                    <div class="row p-3">
                        <div class="col-md-6 col-sm-6 col-12 mb-3">
                            <p><strong>Container Id</strong></p>
                            <div class="my-3 col-md-12">
                              <textarea class="form-control" id="exampleFormControlTextarea1" name="container_id" rows="6"><?= @$result_c['container_id'] ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-12 mb-3">
                            <div class="d-flex justify-content-between">
                                <p><strong>Advanced Customisation</strong></p>
								<div class="form-check form-switch">
                                  <input class="form-check-input test" type="checkbox" id="flexSwitchCheckDefault"  name="is_enabled" <?= $enable ?> />
                                </div>
                            </div>
                            <div class="my-3 col-md-12 advance">
                              <textarea class="form-control" id="exampleFormControlTextarea1" name="css_prop" rows="6"><?= @$result_c['css_prop'] ?></textarea>
                            </div>
                            <div class="normal">
                             <div class="my-3 col-md-12">
                              <p><strong>Background Color</strong></p>
                              <input type="color" id="buttoncolor" name="buttoncolor" pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" value="<?= @$result_c['buttoncolor'] ?>">

                              <p><strong>Text Color</strong></p>
                              <input type="color" id="textcolor" name="textcolor" pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" value="<?= @$result_c['textcolor'] ?>">
                              <p><strong>Outline Color</strong></p>
                              <input type="color" id="outlinecolor" name="outlinecolor" pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" value="<?= @$result_c['outlinecolor'] ?>">
                              </div>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-12 col-12 mb-3">

					 		<label for="inputState" class="form-label">Powered by Revolut logo theme</label>

							<select class="vodiapicker" name="powerby_logo">
					            <option value="en" class="test" data-thumbnail="<?= getenv('app.ASSETSPATH') ?>images/dark.svg">
					            </option>
					            <option value="au" data-thumbnail="<?= getenv('app.ASSETSPATH') ?>images/light.svg"></option>
					      </select>

						<div class="lang-select">
						<button type="button" class="btn-select" value=""></button>
						<div class="b">
						<ul id="a"></ul>
						</div>
						</div>
						</div>
						       <div class="col-md-6 col-sm-12 col-12 mb-3">
												 		    <label  class="form-label">Payment Title</label>
					 		   <input type="text" class="form-control input" id="payment_name" name="payment_name" value="<?= @$result_c['payment_name'] ?>" required maxlength="50">
					 		</div>
                        </div>
					</div>
                  </div>
            </div>
            <div class="row mt-3">
              <div class="col-md-12 text-end">
				<div class="text-right">
					<button type="button" id="resetCustom" class="btn update">Reset</button>&nbsp;&nbsp;&nbsp;
					<button type="submit" class="btn update">Update</button>
				</div>
              </div>
            </div>
			</form>
          </div>
        </div>
      </div>
    </main>
	<?php include 'template/footer.php'; ?>
	<script>
		var text = "Please wait...";
		var current_effect = "bounce";
		var id = '<?= $container_id ?>';
		var buttoncolor = '<?= $buttoncolor ?>';
		var textcolor = '<?= $textcolor ?>';
		var outlinecolor = '<?= $outlinecolor ?>';
		var css = '<?= base64_encode($css_prop) ?>';
		var payment_name = '<?= base64_encode($payment_name) ?>';
		$('body').on('click','#resetCustom',function(){
			$('body #container_id').val(id);
			$('body #buttoncolor').val(buttoncolor);
			$('body #textcolor').val(textcolor);
			$('body #outlinecolor').val(outlinecolor);
			$('body #css_prop').val(window.atob(css));
			$('body #payment_name').val(window.atob(payment_name));
		});
		$('body').on('submit','#updateCustomButton',function(e){
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
			var updated = getUrlParameter('updated');
			if(updated){
				$.toaster({ priority : "success", title : "Success", message : "Revolut Payments Custom button updated for your Store,Please wait for some time and check the changes" });
			}

			$('.test').change(function(){
				if($(this).is(":checked")) {
					$('.advance').addClass('show');
					$('.normal').addClass('hide');


				} else {
					$('.advance').removeClass('show');
					$('.normal').removeClass('hide');
				}
			});

		});
		var langArray = [];
		$('.vodiapicker option').each(function(){
		  var img = $(this).attr("data-thumbnail");
		  var text = this.innerText;
		  var value = $(this).val();
		  var item = '<li><img src="'+ img +'" alt="" value="'+value+'"/><span>'+ text +'</span></li>';
		  langArray.push(item);
		})

		$('#a').html(langArray);

		//Set the button value to the first el of the array
		$('.btn-select').html(langArray[0]);
		$('.btn-select').attr('value', 'en');

		//change button stuff on click
		$('#a li').click(function(){
		   var img = $(this).find('img').attr("src");
		   var value = $(this).find('img').attr('value');
		   var text = this.innerText;
		   var item = '<li><img src="'+ img +'" alt="" /><span>'+ text +'</span></li>';
		  $('.btn-select').html(item);
		  $('.btn-select').attr('value', value);
		  $(".b").toggle();

		});

		$(".btn-select").click(function(){
				$(".b").toggle();
			});

		//check local storage for the lang
		var sessionLang = localStorage.getItem('lang');
		if (sessionLang){
		  //find an item with value of sessionLang
		  var langIndex = langArray.indexOf(sessionLang);
		  $('.btn-select').html(langArray[langIndex]);
		  $('.btn-select').attr('value', sessionLang);
		} else {
		   var langIndex = langArray.indexOf('ch');
		  console.log(langIndex);
		  $('.btn-select').html(langArray[langIndex]);
		}
	</script>
	</body>
</html>