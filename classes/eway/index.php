<!DOCTYPE html>
<html lang="en">
    <head>
        <title>eWAY Rapid 3.1 API PHP Sample Code</title>
        <link href="examples/assets/Styles/Site.css" rel="stylesheet" type="text/css" />
        <link href="examples/assets/Styles/jquery-ui-1.8.11.custom.css" rel="stylesheet" type="text/css" />
        <script src="examples/assets/Scripts/jquery-1.4.4.min.js" type="text/javascript"></script>
        <script src="examples/assets/Scripts/jquery-ui-1.8.11.custom.min.js" type="text/javascript"></script>
        <script src="examples/assets/Scripts/jquery.ui.datepicker-en-GB.js" type="text/javascript"></script>
        <script type="text/javascript" src="examples/assets/Scripts/tooltip.js"></script>
    </head>
    <body>
    <center>
        <div id="outer">
            <div id="toplinks">
                <img alt="eWAY Logo" class="logo" src="examples/assets/Images/companylogo.gif" width="960px" height="65px" />
            </div>
            <div id="main">

                <div id="titlearea">
                    <h1>eWAY Rapid 3.1 API PHP Sample Code</h1>
                </div>

                <div id="maincontent">
                    <div class="response">

                        <h2>Welcome to the eWAY Rapid 3.1 API PHP Sample code!</h2>
                        <br><br>

                        <p><i>Have you got your eWAY Sandbox API Key & Password?</i>
                            If not, <a href="http://go.eway.io/s/article/How-do-I-set-up-my-Sandbox-API-Key-and-password" style="text-decoration: underline;" target="_BLANK">here are some instructions</a>
                            on how to get them</p>
                        <br><br>

                        <h2>Examples</h2>
                        <br>
                        <p>This sample code contains complete examples of the three
                            methods you can process payments with eWAY:</p>
                        <br>
                        <h3><a href="examples/DirectPayment/index.php">Direct Connect</a></h3>
                        <p>Lets you connect directly from your server to eWAY to process a payment.<br>
                            <b>Note:</b> To use this with a live account, eWAY must be provided with proof
                            of a PCI-DSS compliant environment or use <a href="https://eway.io/api-v3/#client-side-encryption" target="_blank">Client Side Encryption</a>.</p>
                        <p>For Sandbox testing, you can enable Direct Connect by logging into your
                            Sandbox MYeWAY and navigating to Settings > Sandbox then read the disclaimer
                            and tick the PCI Enabled box.
                        </p>
                        <br>
                        <h3><a href="examples/ResponsiveSharedPage/index.php">Responsive Shared Page</a></h3>
                        <p>This method sends your customer to an eWAY hosted page to enter their
                            payment details. No PCI compliance hassle!</p>
                        <br>
                        <h3><a href="examples/TransparentRedirect/index.php">Transparent Redirect</a></h3>
                        <p>Using this method, the payment form is hosted on your system, but the payment
                            data is sent directly to eWAY. Once processed, the customer is then seamlessly redirected
                            back to your system. They don't see an eWAY page and your servers have greatly reduced scope
                            of PCI compliance!</p>
                        <br><br>
                        <h2>Questions?</h2>
                        <br>
                        <p>We've got your back! If you need a hand you can</p>
                        <br>
                        <p style="font-size:110%;">Check out the <b>eWAY Community</b>, where you can search hundreds of articles or
                            open a discussion to get help from other developers and eWAY support heroes:<br>
                            <b><a href="https://go.eway.io/s/" target="_blank">https://go.eway.io/s/</a></b>
                        </p>
                        <br>
                        <p>Jump on our website and open a chat with our friendly team</p>
                        <ul>
                            <b>Australia:</b> <a href="http://www.eway.com.au/" target="_BLANK">http://www.eway.com.au/</a><br>
                            <b>New Zealand:</b> <a href="http://eway.io/nz/" target="_BLANK">http://www.eway.co.nz/</a><br>
                            <b>UK:</b> <a href="http://eway.io/uk/" target="_BLANK">http://eway.io/uk/</a><br>
                            <b>Hong Kong:</b> <a href="http://eway.io/hk/" target="_BLANK">http://eway.io/hk/</a><br>
                            <b>Malyasia:</b> <a href="http://eway.io/my/" target="_BLANK">http://eway.io/my/</a><br>
                            <b>Singapore:</b> <a href="http://eway.io/sg/" target="_BLANK">http://eway.io/sg/</a><br>
                        </ul>
                        <p>Or give us a call:</p>
                        <ul>
                            <b>Australia:</b> 1800 10 65 65<br>
                            <b>New Zealand:</b> 0800 392 947<br>
                            <b>UK:</b> 0800 080 3777<br>
                            <b>Hong Kong:</b> 00800 906 586<br>
                            <b>Malaysia:</b> 1800 817 273<br>
                            <b>Singapore:</b> 800 852 3890<br>
                            <b>International:</b> +61 2 6175 3000<br>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="footer"></div>
        </div>
    </center>
</body>
</html>
