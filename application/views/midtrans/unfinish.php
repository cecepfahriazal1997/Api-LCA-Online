<html>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <head>
        <style>
            body {
                background-color: #B33939;
                padding: 16px;
                font-family: 'arial', 'sans-serif';
            }
            img {
                margin-top: 32px;
                width: 72px;
            }
            .img {
                width: 100%;
                text-align: center;
            }
            .title {
                color: white;
                font-size: 22px;
                margin-top: 28px;
            }
            .subtitle {
                color: white;
            }
            .time {
                font-size: 18px;
                font-weight: bold;
            }
            .card {
                background-color: white;
                box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
                transition: 0.3s;
                border-radius: 6px;
                width: 100%;
            }
            .card:hover {
                box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
            }
            .container {
                padding: 2px 16px;
            }
            .label {
                font-size: 12px;
                color: #909090;
                margin-bottom: 0px;
            }
            .value {
                font-size: 16px;
                color: black;
                margin-top: 8px;
            }
            .divider {
                border-top: solid 1px;
                border-left: solid 0px;
                border-right: solid 0px;
                border-bottom: solid 0px;
                border-color: #d9d9d9;
            }
            .button {
                border: none;
                color: black;
                padding: 15px 32px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                margin: 4px 2px;
            }
            .button-success {
                width: 100%;
                background-color: white;
                margin-top: 16px;
            }
            .button-round {
                border-radius: 6px;
            }
        </style>
    </head>
    <body>
        <div class="img">
            <img src="<?= base_url().'assets/images/template/failed.png'?>" />
        </div>
        <h3 class="title">
            Oops, Your order failed to create!
        </h3>
        <p class="subtitle">
            Please try again later,
        </p>
        <div class="card">
            <div class="container">
                <p class="label">
                    Order ID
                </p>
                <p class="value">
                    <?= $transaction->order_id?>
                </p>
                <p class="label">
                    Plan purchased
                </p>
                <p class="value">
                    Plan Premium <?= $region->name?>
                </p>
                <hr class="divider" />
                <p class="label">
                    The amount of the bill
                </p>
                <p class="value">
                    Rp. <?= number_format($transaction->gross_amount, 0, ',', '.')?>
                </p>
            </div>
        </div>
        <button class="button button-success button-round" onclick="funcAndroid.exit();">Back To Dashboard</button>
    </body>
</html>