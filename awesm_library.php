<?php
/*
 PHP awesm library created by Michael Orr 
 twitter @imbiat | company: Cloudspace http://www.cloudspace.com

 main awesm api documentation: http://groups.google.com/group/awesm-api/web/api-documentation

 shorten_url - shortens a url ($target) with the awe.sm service
 be sure to pass in your api key!
 awe.sm encourages you to use correct create_type and share_type values
 if you have a custom domain, be sure to enter the domain parameter

 returns the full awe.sm url

 usage example:
 $new_url = shorten_url(API_KEY,"http://www.cloudspace.com");
 echo $new_url;
 //http://awe.sm/rpb
*/
function shorten_url($api_key, $target, $share_type="other", $create_type="api", $domain="awe.sm"){

	  $data = array(
      'api_key' => $api_key, 
      'version' => '1',
      'domain' => $domain,
      'share_type' => $share_type,
      'create_type' => $create_type,
      'target' => $target,
    );
		$params = array('http' => array('method' => "POST", 'content' => http_build_query($data)));

		$context = stream_context_create($params);
		$fp = @fopen("http://create.awe.sm/url.json", 'rb', false, $context);
		if (!$fp){ return false; }

		$response = @stream_get_contents($fp);
		if ($response === false){
			fclose($fp);
			return false;
		}

		fclose($fp);
    $content = json_decode($response, true);
    return $content['url']['awesm_url'];
}

/*
 get_basic_awesm_info - retrieves basic info for an awe.sm url

 returns an array of:
	awesm_url
	awesm_id
	redirect_url
	--- stats to be returned if permitted
	original_url
	clicks
	share_type
	create_type
	parent_awesm (if present)
	created_at
	sharer_id (SHA-256 hash, if present)

 usage example:
 $basic_data = get_basic_awesm_info($api_key, "rpb");
 
*/
function get_basic_awesm_info($api_key, $stub, $domain="awe.sm"){
  $data = array(
    'api_key' => $api_key, 
    'version' => '1',
    'domain' => $domain
  );
	$params = array('http' => array('method' => "GET", 'content' => http_build_query($data)));
	$context = stream_context_create($params);
	$fp = @fopen("http://create.awe.sm/url/{$stub}.xml", 'rb', false, $context);
	if (!$fp){ return false; } //exit connection bad

	$response = @stream_get_contents($fp);
	if ($response === false){
		fclose($fp);
		return false;
	}

	fclose($fp);
	$content = (array) simplexml_load_string($response);
	return $content;
}

/*
 get_awesm_info_for_original_url - retrieves overal stats (total_clicks and total_shares)
 for the awesm urls in your account that go to the original url specified and details about
 each specific awe.sm url if details=true

 returns an array of:
 total_shares
 total_clicks
 url
   awesm_url
   redirect_url
   original_url
   clicks (in period specified by start_date/end_date, if present)
   share_type
   create_type
   awesm_id
   domain
   parent_awesm (if present)
   created_at
   sharer_id (SHA-256 hash, if present)

 usage example:
 $basic_data = get_awesm_info_for_original_url($api_key, "http://www.cloudspace.com");
 echo $basic_data['total_shares'];
 //2
 foreach($basic_data['url'] as $url){
   echo $url['awesm_url];
 }
 //http://awe.sm/rpb
 //http://awe.sm/5fA

 //a very basic example of how to page through results with this function
  $go_again = true;
  $page = 1;
  $per_page = 100;
  while($go_again){
    $awesm_info = get_awesm_info_for_original_url($AWESM_API_KEY, "http://www.cloudspace.com", $page, $per_page);
    if($awesm_info){
      $results_on_page_cnt = 0;
	  foreach($awesm_info['url'] as $url){
 		$results_on_page_cnt++;
	    $url = (array) $url;
	    //here is where you do what you need to with each awesm url returned
	    if($results_on_page_cnt < $per_page){
		  $go_again = false;
	    } else {
		  $page++;
	    }
     }
   }
 
*/
function get_awesm_info_for_original_url($api_key, $original_url, $page=1, $per_page=100, $details="TRUE", $domain="awe.sm"){
  $data = array(
    'api_key' => $api_key, 
    'version' => '1',
    'domain' => $domain,
    'details' => $details,
    'original_url' => $original_url,
    'page' => $page,
    'per_page' => $per_page
  );
	$params = array('http' => array('method' => "GET", 'content' => http_build_query($data)));
	$context = stream_context_create($params);
	$fp = @fopen("http://create.awe.sm/url.xml", 'rb', false, $context);
	if (!$fp){ return false; } //exit connection bad

	$response = @stream_get_contents($fp);
	if ($response === false){
		fclose($fp);
		return false;
	}

	fclose($fp);
	$content = (array) simplexml_load_string($response);
	return $content;
}

?>