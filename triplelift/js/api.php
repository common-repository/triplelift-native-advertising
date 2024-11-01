<script type="text/javascript">
var triplelift_np_admin_api = triplelift_np_admin_api || {};

(function() {

  function api_get(url, callback, data, asynctype) {
    if (typeof data === "undefined" || !data) {
      var data = {};
    }
    
    var async = asynctype ? asynctype : true;
    
    jQuery.ajax({
      type : 'GET',
      data : data,
      url : "<?php print TRIPLELIFT_NP_API_URL; ?>" + url,
      async : async,
      error : callback,
      success : callback,
      crossDomain : true,
      dataType : 'json',
      contentType : "application/json",
      headers : {
        "Auth-token" : "<?php print $this->auth_token ?>",
        "Accept" : "application/json"
      }
    });
  }

  function api_put(url, data, callback) {
    
      return jQuery.ajax({
          type : 'PUT',
          url : "<?php print TRIPLELIFT_NP_API_URL; ?>" + url,
          data : JSON.stringify(data),
          dataType : 'json',
          crossDomain : true,
          error : callback,
          success : callback,
          contentType : "application/json",
          headers : {
            "Auth-token" :"<?php print $this->auth_token ?>",
            "Accept" : "application/json"
          }
      });
  }

  function api_post(url, data, callback) {
      var options = {
          type : 'POST',
          url : "<?php print TRIPLELIFT_NP_API_URL; ?>" + url,
          data : JSON.stringify(data),
          dataType : 'json',
          crossDomain : true,
          error : callback,
          success : callback,
          contentType : "application/json",
      };
      if (url == '/login') {
        options.headers = {
            "Accept" : "application/json"
        };
      } else {
        options.headers = {
            "Accept" : "application/json",
            'Auth-token' : "<?php print $this->auth_token ?>",
        };
      }
      return jQuery.ajax(options);
    }

    triplelift_np_admin_api.get = api_get;
    triplelift_np_admin_api.put = api_put;
    triplelift_np_admin_api.post = api_post;
})();
</script>
