<!doctype html>
<html>

<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo esc_html( $title ) ?></title>
	<style>
		/* -------------------------------------
          FONTS
      ------------------------------------- */

		@import url('https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap');


		/* -------------------------------------
          GLOBAL RESETS
      ------------------------------------- */

		/*All the styling goes here*/

		img {
			border: none;
			-ms-interpolation-mode: bicubic;
			max-width: 100%;
		}

		body {
			background-color: #f6f6f6;
			font-family: 'Source Sans Pro', sans-serif;
			-webkit-font-smoothing: antialiased;
			font-size: 14px;
			line-height: 1.4;
			margin: 0;
			padding: 0;
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: 100%;
		}

		table {
			border-collapse: separate;
			mso-table-lspace: 0pt;
			mso-table-rspace: 0pt;
			width: 100%;
		}

		table td {
			font-family: 'Source Sans Pro', sans-serif;
			font-size: 14px;
			vertical-align: top;
		}

		/* -------------------------------------
          BODY & CONTAINER
      ------------------------------------- */

		.body {
			background-color: #f6f6f6;
			width: 100%;
		}

		/* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
		.container {
			display: block;
			margin: 0 auto !important;
			/* makes it centered */
			max-width: 800px;
			padding: 20px 10px 10px;
			min-width: 500px;
		}

		/* This should also be a block element, so that it will fill 100% of the .container */
		.content {
			box-sizing: border-box;
			display: block;
			margin: 0 auto;
			max-width: 800px;
			padding: 20px 10px 10px;
			min-width: 500px;
		}

		/* -------------------------------------
          HEADER, FOOTER, MAIN
      ------------------------------------- */
		.main,
		.header {
			background: #ffffff;
			border-radius: 0 0 3px 3px;
			width: 100%;
		}

		.header {
			background-color: #f15a29;
			background-size: cover;
			font-size: 18px;
			color: #ffffff;
			letter-spacing: 0.5px;
			border-radius: 3px 3px 0 0;
		}

		.wrapper {
			box-sizing: border-box;
			padding: 30px;
		}

		.header .wrapper {
			padding: 20px 30px 18px;
		}

		.content-block {
			padding-bottom: 10px;
			padding-top: 10px;
		}

		.footer {
			clear: both;
			margin-top: 10px;
			text-align: center;
			width: 100%;
		}

		.footer td,
		.footer p,
		.footer span,
		.footer a {
			color: #999999;
			font-size: 12px;
			text-align: center;
		}

		.billing-shipping-address {
			margin-bottom: 25px;
		}

		.billing-shipping-address tr td {
			padding-right: 20px;
		}

		.logo-address {
			text-align: center;
			margin-top: 50px;
		}

		.logo-address td p {
			color: #747f84;
			font-size: 11px;
			margin-bottom: 0;
			margin-top: 10px;
		}

		.logo-address img {
			max-width: 100px;
			width: 100px;
			height: auto;
		}


		/* -------------------------------------
          TYPOGRAPHY
      ------------------------------------- */
		h1,
		h2,
		h3,
		h4 {
			color: #000000;
			font-family: 'Source Sans Pro', sans-serif;
			font-weight: 400;
			line-height: 1.4;
			margin: 0;
			margin-bottom: 30px;
		}

		h1 {
			font-size: 35px;
			font-weight: 300;
			text-align: center;
			text-transform: capitalize;
		}

		h2 {
			font-size: 14px;
			color: #586469;
			letter-spacing: 0.5px;
			text-transform: uppercase;
			font-weight: 700;
			margin-top: 30px;
			margin-bottom: 12px;
		}

		.header h1 {
			font-size: 18px;
			text-align: left;
			text-transform: capitalize;
			margin: 0;
			font-weight: 700;
			color: #ffffff;
		}

		p,
		ul,
		ol {
			font-family: 'Source Sans Pro', sans-serif;
			font-size: 14px;
			font-weight: normal;
			margin: 0;
			margin-bottom: 20px;
			color: #586469;
			text-decoration: none solid rgb(88, 100, 105);
			line-height: 20px;
			letter-spacing: 0.5px;
		}

		ul,
		ol {
			padding: 0;
		}

		p li,
		ul li,
		ol li {
			list-style-position: inside;
			margin-left: 0;
			list-style: none;
		}

		a {
			color: #3498db;
			text-decoration: underline;
		}


		/* -------------------------------------
          ORDER DETAILS
      ------------------------------------- */
		.order-details {
			border: 1px solid #e3e3e3;
			margin-bottom: 10px;
			background: #FBFCFD;
		}

		.order-details td,
		.order-details th {
			color: #586469;
		}

		.order-details tr td,
		.order-details tr th {
			text-align: left;
		}

		.order-details tr td+td,
		.order-details tr td+th,
		.order-details tr th+td,
		.order-details tr th+th {
			text-align: right;
		}

		.order-details-top,
		.order-details-middle,
		.order-details-bottom {
			padding: 5px 18px;
		}

		.order-details-top tr,
		.order-details-middle tr {
			height: 40px;
		}

		.order-details-top tr td,
		.order-details-top tr th {
			vertical-align: middle;
			border-bottom: 1px solid #e3e3e3;
		}

		.order-details-top .no-border td,
		.order-details-top .no-border th {
			border: none;
		}

		.order-details-middle tr td,
		.order-details-middle tr th {
			vertical-align: middle;
		}

		.order-details-middle {
			border-top: 1px solid #e3e3e3;
			border-bottom: 1px solid #e3e3e3;
			background-color: #f2f5f7;
		}

		.order-details-bottom tr {
			height: 50px;
		}

		.order-details-bottom tr th {
			vertical-align: middle;
			color: #f15a29;
			font-size: 18px;
			font-weight: 700;
		}

		.text-orange,
		.order-details .text-orange {
			color: #f15a29 !important;
		}

		/* -------------------------------------
          BUTTONS
      ------------------------------------- */
		.btn {
			box-sizing: border-box;
			width: 100%;
			margin-bottom: 7px;
		}

		.btn>tbody>tr>td {
			padding-bottom: 15px;
		}

		.btn table {
			width: auto;
		}

		.btn table td {
			background-color: #ffffff;
			border-radius: 5px;
			text-align: center;
		}

		.btn a {
			background-color: #ffffff;
			border: solid 1px #f15a29;
			border-radius: 5px;
			box-sizing: border-box;
			color: #3498db;
			cursor: pointer;
			display: inline-block;
			font-size: 14px;
			font-weight: bold;
			margin: 0;
			padding: 12px 25px 10px;
			text-decoration: none;
			text-transform: capitalize;
		}

		.btn-primary table td {
			background-color: #3498db;
		}

		.btn-primary a {
			background-color: #f15a29;
			border-color: #f15a29;
			color: #ffffff;
		}

		/* -------------------------------------
          OTHER STYLES THAT MIGHT BE USEFUL
      ------------------------------------- */
		.last {
			margin-bottom: 0;
		}

		.first {
			margin-top: 0;
		}

		.align-center {
			text-align: center;
		}

		.align-right {
			text-align: right;
		}

		.align-left {
			text-align: left;
		}

		.clear {
			clear: both;
		}

		.mt0 {
			margin-top: 0;
		}

		.mb0 {
			margin-bottom: 0;
		}

		.preheader {
			color: transparent;
			display: none;
			height: 0;
			max-height: 0;
			max-width: 0;
			opacity: 0;
			overflow: hidden;
			mso-hide: all;
			visibility: hidden;
			width: 0;
		}

		.powered-by a {
			text-decoration: none;
			font-weight: 600;
		}

		.powered-by a:hover {
			text-decoration: underline;
		}

		hr {
			border: 0;
			border-bottom: 1px solid #f6f6f6;
			margin: 20px 0;
		}

		strong, b, th {
			font-weight: 600;
		}
        /* -------------------------------------
              QRIS CODE
          ------------------------------------- */
        .qris img {
            width: 100%;
            max-width: 170px;
        }
		/* -------------------------------------
          RESPONSIVE AND MOBILE FRIENDLY STYLES
      ------------------------------------- */
		@media only screen and (max-width: 620px) {

			body, .body {
				background-color: #ffffff !important;
				margin-bottom: 20px !important;
			}

			.header {
				margin-bottom: 10px !important;
				border-radius: 0 !important;
			}

			table[class=body] h1 {
				font-size: 28px !important;
			}

			table[class=body] p,
			table[class=body] ul,
			table[class=body] ol,
			table[class=body] td,
			table[class=body] span,
			table[class=body] a {
				font-size: 16px !important;
			}

			table[class=body] .wrapper,
			table[class=body] .article {
				padding: 10px !important;
			}

			table[class=body] .content {
				padding: 0 !important;
			}

			table[class=body] .container {
				padding: 0 !important;
				width: 100% !important;
			}

			table[class=body] .main {
				border-left-width: 0 !important;
				border-radius: 0 !important;
				border-right-width: 0 !important;
			}

			table[class=body] .btn table {
				width: 100% !important;
			}

			table[class=body] .btn a {
				width: 100% !important;
			}

			table[class=body] .img-responsive {
				height: auto !important;
				max-width: 100% !important;
				width: auto !important;
			}
		}

		/* -------------------------------------
          PRESERVE THESE STYLES IN THE HEAD
      ------------------------------------- */
		@media all {
			.ExternalClass {
				width: 100%;
			}

			.ExternalClass,
			.ExternalClass p,
			.ExternalClass span,
			.ExternalClass font,
			.ExternalClass td,
			.ExternalClass div {
				line-height: 100%;
			}

			.apple-link a {
				color: inherit !important;
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				text-decoration: none !important;
			}

			#MessageViewBody a {
				color: inherit;
				text-decoration: none;
				font-size: inherit;
				font-family: inherit;
				font-weight: inherit;
				line-height: inherit;
			}

			.btn-primary table td:hover {
				background-color: #34495e !important;
			}

			.btn-primary a:hover {
				background-color: #d55d37 !important;
				border-color: #d55d37 !important;
			}
		}
	</style>
</head>

<body class="">
	<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
		<tr>
			<td class="container">
				<div class="content">

					<!-- START CENTERED WHITE CONTAINER -->
					<table role="presentation" class="header">

						<!-- START MAIN CONTENT AREA -->
						<tr>
							<td class="wrapper">
								<h1><?php echo esc_html( $title ) ?></h1>
							</td>
						</tr>
					</table>


					<!-- START CENTERED WHITE CONTAINER -->
					<table role="presentation" class="main">

						<!-- START MAIN CONTENT AREA -->
						<tr>
							<td class="wrapper">
								<table role="presentation" border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td>
											<?php echo wp_kses_post( $content ) ?>

											<table role="presentation" border="0" cellpadding="0" cellspacing="0"
												class="logo-address">
												<tr>
													<td>
														<?php if ( $store_logo != null ) : ?>
															<img src="<?php echo esc_url( $store_logo ) ?>">
														<?php endif ?>

														<p><?php echo esc_html( $store_address ) ?></p>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>

						<!-- END MAIN CONTENT AREA -->
					</table>
					<!-- END CENTERED WHITE CONTAINER -->

					<!-- START FOOTER -->
					<div class="footer">
						<table role="presentation" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td class="content-block powered-by">
									<?php
										$powered_by_label = new Commercioo_Powered_By_Label(); 
										$powered_by_label->render_text_only();
									?>
								</td>
							</tr>
						</table>
					</div>
					<!-- END FOOTER -->

				</div>
			</td>
		</tr>
	</table>
</body>

</html>