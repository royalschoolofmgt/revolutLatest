<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="Author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
		<meta name="generator" content="Hugo 0.83.1">
		<title>Revolut Payments</title>
		<!-- Bootstrap core CSS -->
		<link href="<?= getenv('app.ASSETSPATH') ?>css/bootstrap.min.css" rel="stylesheet">
		<!-- font-awesome css-->
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">

		<link href="<?= getenv('app.ASSETSPATH') ?>css/custom.css" rel="stylesheet">
		<link href="<?= getenv('app.ASSETSPATH') ?>css/style.css" rel="stylesheet">

		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="<?= getenv('app.ASSETSPATH') ?>css/toaster/toaster.css">
		<link rel="stylesheet" href="<?= getenv('app.ASSETSPATH') ?>css/247revolutloader.css">
	</head>
	<body>
		<div class="nav-section header-image p-3">
			<nav class="navbar navbar-light navbar-header p-lg-0">
				<a class="navbar-brand" href="#">
					<img src="<?= getenv('app.ASSETSPATH') ?>images/pay.png" alt="">
				</a>
				<div class="navbar-text">
					<a href="<?= getenv('app.baseURL') ?>settings/customButton" ><button class="btn btn-text-green" type="button">Customise Payment Button</button></a>
					<a href="<?= getenv('app.baseURL') ?>home/orderDetails" ><button class="btn btn-green order-mobile" type="button">Order Details</button></a>
				</div>
			</nav>
		</div>