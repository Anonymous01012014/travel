<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

	/**
	 * file name : curl_helper
	 * 
	 * Description :
	 * This file contains functions to deal with curl requests
	 * 
	 * Created date ; 26-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */    


	/**
	 * function name : send_request
	 * 
	 * Description : 
	 * This function sends curl request with the given url and returns the response body
	 * 
	 * parameters:
	 * url: the url to send the request to.
	 * 
	 * Created date : 26-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 function send_request($url){
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);//for https requests
		$head = curl_exec($ch); 
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($head, 0, $header_size);
		$body = substr($head, $header_size);
		curl_close($ch); 
		return $body;
	 }
