<div class="adblockwarning" id="adblockwarning" style="display:none;">
<h3>Warning!</h3>
<p>It looks like you have Adblock Plus active, which prevents this plugin from working. Please <a target="_blank" href="https://adblockplus.org/en/faq_basics#disable">disable</a> Adblock Plus and refresh in order to login.</p>
</div>

<div class="triplelift_np_admin_dim" id="adblockwarningbg" style="display:none;"></div>
<script src="http://console.triplelift.com/app/libs/advertisements.js"></script>

<script type="text/javascript">
// checking for Adblock plus, show message if it is active

function isInPage(node) {
return (node === document.body) ? false : document.body.contains(node);
}

function showAdblockWarningMessage(){
    
    var warningmessage = document.getElementById('adblockwarning');
    warningmessage.style.display = 'block';
    var warningmessagebg = document.getElementById('adblockwarningbg');
    warningmessagebg.style.display = 'block';
}

if (!isInPage(document.getElementById('TestAdBlock'))){
showAdblockWarningMessage();
}
</script>
