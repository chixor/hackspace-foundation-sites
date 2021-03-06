<? 
$page = 'donate';
require( './header.php' );
?>

<h2>Donate</h2>
<p>London Hackspace is a non-profit organisation which provides facilites and hosts events for the geek community in London.
    It costs us <a href="/cost-of-hacking/">more than £10,000 per month</a>
    to keep our doors open. Your donation can help us make our space more comfortable, buy better tools,
    and run more events.</p>

<p>Here's how to donate to London Hackspace:</p>

<h3>Paypal</h3>
<p>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick" />
        <input type="hidden" name="hosted_button_id" value="3741369" />
        <input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donateCC_LG.gif" style="border:0;" name="submit" alt="PayPal - The safer, easier way to pay online." />
        <img alt="" style="border:0;" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
    </form>
</p>


<h3>Bitcoin</h3>
<p><a class="coinbase-button" data-code="02f847c530bf2538491bb3c3da5dd851" data-button-style="custom_large" href="https://coinbase.com/checkouts/02f847c530bf2538491bb3c3da5dd851">Donate Bitcoins</a><script src="https://coinbase.com/assets/button.js" type="text/javascript"></script></p>

<h3>Bank Transfer</h3>
<? if ($user) { ?>
<p>Our bank details are as follows:</p>

<table>
    <tr>
        <th>Bank</th>
        <td>Barclays</td>
    </tr>
    <tr>
        <th>Payee</th>
        <td>London Hackspace Ltd.</td>
    </tr>
    <tr>
        <th>Sort Code</th>
        <td>20-32-06</td>
    </tr>
    <tr>
        <th>Account Number</th>
        <td>53413292</td>
    </tr>
</table>

<p>Don't worry if the name doesn't fit in full.</p>

<? } else { ?>
<p>Due to direct debit fraud, we require you to <a href="/login.php">log in</a> before you can see our bank details.</p>
<? } ?>

<?php require('./footer.php'); ?>
</body>
</html>
