{strip}{* gateway success template *}
{if $status == 'SUCCESS'}

  <div class="alert alert-info">
  {if !empty($message)}
    {$message} {* message from the gateway itself *}
  {else}
    Your transaction ID:{$transaction_id} has been completed, and a receipt for your purchase has been emailed to you at {$email_address}. Thank you for shopping at {sitename}.
  {/if}
  </div>

{elseif $status == 'PENDING' || $status == 'payment_none' }

  <div class="alert alert-info">
    {if !empty($message)}
      {$message}
    {else}
      <p>Thank you for shopping with {sitename}.  Your order is processing, and awaiting final approval from the financial institution.  When this completes, you will receive an email containing the complete order information.</p>
      <p>Have a nice day!</p>
    {/if}
  </div>


{elseif $status == 'COMPLETED'}

  <div class="alert alert-info">Thank you for shopping with {sitename}.  For your convenience your cart has been cleared.  We hope you enjoy your purchases.</div>

{elseif $status == 'CANCELLED'}

  <div class="alert alert-info">Your transaction has been cancelled at your request.  We hope you continue to shop with us in the future.</div>

{else}

  <div class="alert alert-danger">
    <p>Your transaction was not completed.  Either the operation was cancelled or some other error occurred.  Please confirm your payment information and try again.  We apologize for this inconvenience.  Thank you for your time.</p>
    {if isset($error) && $error}
      <p>Error: {$error}</p>
    {/if}
    {if isset($error_message) && $error_message}
      {* this is from the gateway itself. *}
      <p>Gateway Message: {$error_message}</p>
    {/if}
  </div>

{/if}{/strip}
