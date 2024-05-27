<!DOCTYPE html>
<html lang="en">

<head>

	<link rel="shortcut icon" href="<?php echo base_url("assets") ?>/images/logo_bappenas.png">

	<title><?php echo $page_title ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--===============================================================================================-->
	<link rel="icon" type="image/png" href="<?php echo base_url("package") ?>/plugins/login_v1/images/icons/favicon.ico" />
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/vendor/bootstrap/css/bootstrap.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/vendor/animate/animate.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/vendor/css-hamburgers/hamburgers.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/vendor/animsition/css/animsition.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/vendor/select2/select2.min.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/vendor/daterangepicker/daterangepicker.css">
	<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/css/util.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("package") ?>/plugins/login_v1/css/main.css">
	<!--===============================================================================================-->

	<!-- Style from index.php (Oldest login template) -->
	<!-- Base Css Files -->
	<link href="<?php echo base_url("package") ?>/css/bootstrap.min.css" rel="stylesheet" />

	<!-- Font Icons -->
	<link href="<?php echo base_url("package") ?>/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
	<link href="<?php echo base_url("package") ?>/plugins/ionicon/css/ionicons.min.css" rel="stylesheet" />
	<link href="<?php echo base_url("package") ?>/css/material-design-iconic-font.min.css" rel="stylesheet">

	<!-- animate css -->
	<link href="<?php echo base_url("package") ?>/css/animate.css" rel="stylesheet" />

	<!-- Waves-effect -->
	<link href="<?php echo base_url("package") ?>/css/waves-effect.css" rel="stylesheet">

	<link href="<?php echo base_url("package") ?>/plugins/sweetalert/dist/sweetalert2.min.css" rel="stylesheet">

	<!-- Custom Files -->
	<link href="<?php echo base_url("package") ?>/css/helper.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url("package") ?>/css/style.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url(); ?>/package/css/userdefined.css" rel="stylesheet" />
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="<?php echo base_url("package") ?>/https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="<?php echo base_url("package") ?>/https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->

	<script src="<?php echo base_url("package") ?>/js/modernizr.min.js"></script>
	<style type="text/css">
		.singtext {
			font-size: 20px;
			color: #0088D8;
			font-weight: 500;
			letter-spacing: 1px;
		}
	</style>
	<!-- End style from index.php (Oldest version login template) -->

</head>

<body style="background-color: #666666;">

	<div class="limiter">
		<div class="container-login100">
			<!-- <div class="wrap-login100" style="background-image: url('../bg_login.png'); background-repeat: no-repeat; background-size: cover;"> -->
			<div class="wrap-login100" style="background-image: url('<?php echo base_url("assets/images/bacground_login.png") ?>'); background-repeat: no-repeat; background-size: 60%;">
				<form id="frm_login" class="login100-form validate-form" style="width: 550px; background-color: rgba(255, 255, 255, 1); padding-top: 50px;">
					<!-- <form class="login100-form validate-form" style="width: 485px; background-color: rgba(255, 255, 255, 1); padding-top: 70px;"> -->
					<!-- <form class="login100-form validate-form" style="width: 485px; background-color: white; padding-top: 130px;"> -->

					<div class="card m-b-20" style="background-image: linear-gradient(to left, #DF0241 30%, #0017ba 100%);">
						<div class="card-body">
							<span class="login100-form-title text-light" style="font-size: 3rem;">
								<b>Dashboard Pemantauan</b>
							</span>
						</div>
					</div>

					<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" id="csrf" value="<?php echo $this->security->get_csrf_hash(); ?>" />

					<div class="wrap-input100 validate-input" data-validate="ID Login is required">
						<input class="input100" type="text" name="userid" required="">
						<span class="focus-input100"></span>
						<span class="label-input100">ID Login</span>
					</div>


					<div class="wrap-input100 validate-input" data-validate="Password is required">
						<input class="input100" type="password" name="pass">
						<span class="focus-input100"></span>
						<span class="label-input100">Password</span>
					</div>



					<!-- <div class="form-group input_wrapper " style="margin-top: 5px; width: 50%;">
						<div id="captcha_wrapper" style="padding: 3px;border:1px solid #eee;text-align: center;" title="Klik untuk refresh captcha">
							<?php echo $captcha_img; ?>
						</div>
					</div>

					<div class="wrap-input100 validate-input" data-validate="Security code is required">
						<input id="captcha" data-caption="Captcha" class="input100" type="text" name="captcha">
						<span class="focus-input100"></span>
						<span class="label-input100">Security Code</span>
					</div> -->


					<div class="container-login100-form-btn">
						<button class="login100-form-btn" type="submit">
							Login
						</button>
					</div>
					<div class="m-login__account">
						<span class="m-login__account-msg">
							Tidak Memiliki Akun?
						</span>
						&nbsp;&nbsp;
						<a href="/peppd/Home/demo/" class="m-link m-link--light m-login__account-link">
							Masuk ke Dashboard Demo
						</a>
					</div>
					<div class="text-center p-t-46">
						<span class="txt2">
							PEPPD Bappenas © Copyright 2021. All Rights Reserved.
						</span>
					</div>
				</form>

				<!-- <div class="login100-more" style="background-image: url('login_v1/images/bg-01.jpg'); width: calc(100% - 485px);">
				</div> -->
			</div>
		</div>
	</div>





	<!--===============================================================================================-->
	<!-- <script src="<?php echo base_url("package") ?>/plugins/login_v1/vendor/jquery/jquery-3.2.1.min.js"></script> -->
	<script src="<?php echo base_url("package") ?>/plugins/login_v1/vendor/jquery/jquery-3.7.1.min.js"></script>
	<!--===============================================================================================-->
	<script src="<?php echo base_url("package") ?>/plugins/login_v1/vendor/animsition/js/animsition.min.js"></script>
	<!--===============================================================================================-->
	<script src="<?php echo base_url("package") ?>/plugins/login_v1/vendor/bootstrap/js/popper.js"></script>
	<script src="<?php echo base_url("package") ?>/plugins/login_v1/vendor/bootstrap/js/bootstrap.min.js"></script>
	<!--===============================================================================================-->
	<script src="<?php echo base_url("package") ?>/plugins/login_v1/vendor/select2/select2.min.js"></script>
	<!--===============================================================================================-->
	<script src="<?php echo base_url("package") ?>/plugins/login_v1/vendor/daterangepicker/moment.min.js"></script>
	<script src="<?php echo base_url("package") ?>/plugins/login_v1/vendor/daterangepicker/daterangepicker.js"></script>
	<!--===============================================================================================-->
	<script src="<?php echo base_url("package") ?>/plugins/login_v1/vendor/countdowntime/countdowntime.js"></script>
	<!--===============================================================================================-->
	<script src="<?php echo base_url("package") ?>/plugins/login_v1/js/main.js"></script>

	<!-- Script from index.php (Oldest version login template) -->
	<script>
		var resizefunc = [];
	</script>
	<!-- <script src="<?php echo base_url("package") ?>/js/jquery.min.js"></script> -->
	<script src="<?php echo base_url("package") ?>/js/jquery-3.7.1.min.js"></script>
	<script src="<?php echo base_url("package") ?>/js/bootstrap.min.js"></script>
	<script src="<?php echo base_url("package") ?>/js/waves.js"></script>
	<script src="<?php echo base_url("package") ?>/js/wow.min.js"></script>
	<script src="<?php echo base_url("package") ?>/js/jquery.nicescroll.js" type="text/javascript"></script>
	<script src="<?php echo base_url("package") ?>/js/jquery.scrollTo.min.js"></script>
	<script src="<?php echo base_url("package") ?>/plugins/jquery-detectmobile/detect.js"></script>
	<script src="<?php echo base_url("package") ?>/plugins/fastclick/fastclick.js"></script>
	<script src="<?php echo base_url("package") ?>/plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
	<script src="<?php echo base_url("package") ?>/plugins/jquery-blockui/jquery.blockUI.js"></script>
	<script src="<?php echo base_url("package") ?>/plugins/jquery-validation-1.15.0/dist/jquery.validate.min.js"></script>
	<script src="<?php echo base_url("package") ?>/plugins/sweetalert/dist/sweetalert2.all.min.js"></script>


	<!-- CUSTOM JS -->
	<script src="<?php echo base_url("package") ?>/js/universal.js"></script>
	<script src="<?php echo base_url("package") ?>/js/admin/login/login.js"></script>
	<script type="text/javascript">
		<?php
		if (isset($js_initial))
			echo $js_initial;
		?>
		$(window).load(function() {
			// Animate loader off screen
			$(".se-pre-con").fadeOut("slow");;
		});
	</script>
	<!-- End script from index.php (Oldest version login template) -->


</body>

</html>