<?php
date_default_timezone_set('Asia/Bangkok');
error_reporting(0);
class KasikornBank
{

    public $credentials = array();
    public $cookie_file = null;
    public $online_gateway = "https://kbiz.kasikornbank.com";
    public $ib_gateway = "https://kbiz.kasikornbank.com/services";
    public $curl_options = null;
    public $dataRsso = null;
    

    public function __construct($username = null, $password = null, $userProfiles = 0, $language = "en", $cookie_path = null)
    {
        if (!is_null($username) && !is_null($password)) {
            $this->credentials["username"] = strval($username);
            $this->credentials["password"] = strval($password);
            $this->credentials["userProfiles"] = strval($userProfiles);
            $this->credentials["language"] = strval($language);
        }
        $this->cookie_file = $cookie_path;
    }


    public function request($method, $endpoint, $headers = array(), $data = null)
    {
        $handle = curl_init();
        if (!is_null($data)) {
            if (is_array($data)) {
                # code...
                curl_setopt($handle, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
                
            }else{
                if ($data == 'emty') {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, '{}');
                }else{
                    curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
                }
               
            }
           
            if (is_array($data)) $headers = array_merge(array("Content-Type" => "application/json"), $headers);
        }
        
        curl_setopt_array($handle, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_COOKIEFILE => $this->cookie_file,
            CURLOPT_COOKIEJAR => $this->cookie_file,
            CURLOPT_HTTPHEADER => $this->buildHeaders($headers)
        ));
        if (is_array($this->curl_options)) curl_setopt_array($handle, $this->curl_options);
        $response = curl_exec($handle);

        $http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $res_header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $res_headers = [];
        foreach (explode("\r\n", trim($res_header)) as $row) {
            if (preg_match('/(.*?): (.*)/', $row, $matches)) {
                $res_headers[$matches[1]] = $matches[2];
            }
        }
        if ($result = json_decode($body, true)) {
            return array("code" => $http_code, "header" => $res_headers, "body" => $result);;
        }
        return array("code" => $http_code, "header" => $res_headers, "body" => $body);
    }

    
    public function buildHeaders($array)
    {
        $headers = array();
        foreach ($array as $key => $value) {
            $headers[] = $key . ": " . $value;
        }
        return $headers;
    }


    public function Login()
    {
        $this->request("POST", $this->online_gateway . "/authen/login.do", array(
            "Content-Type" => "application/x-www-form-urlencoded"
        ), http_build_query(array(
            "userName" => $this->credentials["username"],
            "password" => $this->credentials["password"],
            "tokenId" => "0",
            "cmd" => "authenticate",
            "locale" => $this->credentials["language"],
            "custType" => "",
            "captcha" => "",
            "app" => "0"
        )));
        $redirectToIB = "https://kbiz.kasikornbank.com/authen/ib/redirectToIB.jsp";
        $redirect_res = $this->request("GET", $redirectToIB);
        if (preg_match('/window\.top\.location\.href = "(.*)";/', $redirect_res["body"], $matches)) {
            $url_dataRsso = $matches[1];
            $dataRsso = explode("dataRsso=", $url_dataRsso)[1];
            $this->dataRsso = $dataRsso;
            $this->request("GET", $url_dataRsso);
            return $this->Session($dataRsso);
        }
        return false;
    }

    public function Session($dataRsso)
    {
        $res_vs = $this->request("POST", $this->ib_gateway . "/api/authentication/validateSession", array(
            "Content-Type" => "application/json",
            "Accept" => "application/json, text/plain, */*"
        ), array(
            "dataRsso" => $dataRsso,
        ));
        $this->credentials["token"] = $res_vs["header"]["x-session-token"];
        $profile = $res_vs["body"]["data"]["userProfiles"][$this->credentials["userProfiles"]];
        $this->credentials["ownerId"] = $profile["ibId"];
        $this->credentials["ownerType"] = $profile["roleList"]["0"]["roleName"];
        $this->credentials["companyId"] = $profile["companyId"];
        $this->Session_refresh();
        return $res_vs;
    }


    public function Session_refresh()
    {
        $date = new DateTime();
        $formattedDate = $date->format('YmdHisu');
        $res_rs = $this->request("POST", $this->ib_gateway . "/api/refreshSession", array(
            "Accept" => "application/json, text/plain, */*",
            "Authorization" => $this->credentials["token"],
            "Content-Type" => "application/json",
            "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.43",
            "Accept-Encoding" => "gzip, deflate, br",
            "X-Ib-Id" =>  $this->credentials["ownerId"],
            "X-Re-Fresh" => "Y",
            "X-Request-Id" => $formattedDate,
            "X-Session-Ibid" => $this->credentials["ownerId"],
            "X-Url" => "https://kbiz.kasikornbank.com/login?dataRsso=" . $this->dataRsso,
        ), "{}");
        $this->credentials["token"] = $res_rs["header"]["x-session-token"];
        return json_encode($res_rs['body']);
    }

    
    public function recaptchaBypass()
    {

      $createTask = $this->request("POST", 'https://api.anti-captcha.com/createTask', array(
            "Accept" => "application/json",
            "Content-Type" => "application/json",
        ),
        array(
            "clientKey"=>"332b9ccdf83c471f80ffcf5a4681bdac",
            "task"=>[
                "type"=>"RecaptchaV2TaskProxyless",
                "websiteURL"=>"https://kbiz.kasikornbank.com/menu/fundtranfer/fundtranfer",
                "websiteKey"=>"6Lecz88aAAAAADMoaLKBfDC7s9faxdiaYWiCYL8w"
            ],
            "softId"=>0,
            "languagePool"=>"en"
        )
        );

        if ($createTask['body']['errorId'] == 0) {

            $getTaskResult = $this->request("POST", 'https://api.anti-captcha.com/getTaskResult', array(
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ),
            array(
                "clientKey"=>"332b9ccdf83c471f80ffcf5a4681bdac",
                "taskId" => $createTask['body']['taskId']
            )
            );

            if ($getTaskResult['body']['status'] == "ready") {
                $this->credentials["recaptcha"] = $getTaskResult['body']['solution']['gRecaptchaResponse'];
                echo  $this->credentials["recaptcha"];
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function recaptchaVerify()
    {

       $recaptchaBypass =  $this->recaptchaBypass();

        $res_rs = $this->request("POST", $this->ib_gateway . "/api/authentication/recaptchaVerify", array(
            "Accept" => "application/json, text/plain, */*",
            "Authorization" => $this->credentials["token"],
            "Content-Type" => "application/json",
            "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.43",
            "Accept-Encoding" => "gzip, deflate, br",
            "X-Ib-Id" => $this->credentials["ownerId"],
            "X-Re-Fresh" => "Y",
            "X-Session-Ibid" => $this->credentials["ownerId"],
        ), array("recaptchaResponse" => $this->credentials["recaptcha"]));
        $this->credentials["token"] = $res_rs["header"]["x-session-token"];
        return json_encode($res_rs['body']);
        
        
    }


    public function getBankAccount($checkBalance = "N", $accType = "CA,SA,FD", $nicknameType = "OWNAC")
    {

        $res_acc = $this->request("POST", $this->ib_gateway . "/api/bankaccountget/getOwnBankAccountFromList", array(
            "Content-Type" => "application/json",
            "X-Ib-Id" =>  $this->credentials["ownerId"],
            "Authorization" => $this->credentials["token"]
        ), array(
            "accountType" => $accType,
            "checkBalance" => $checkBalance,
            "custType" => "I",
            "language" => $this->credentials["language"],
            "nicknameType" => $nicknameType,
            "ownerId" => $this->credentials["ownerId"],
            "ownerType" => $this->credentials["ownerType"]
        ));
        return $res_acc["body"]["data"];
    }

    public function getAccountSummary()
    {
        $res_asy = $this->request("POST", $this->ib_gateway . "/api/accountsummary/getAccountSummaryList", array(
            "Content-Type" => "application/json",
            "X-Ib-Id" =>  $this->credentials["ownerId"],
            "Authorization" => $this->credentials["token"]
        ), array(
            "custType" => "I",
            "isReload" => "N",
            "lang" => $this->credentials["language"],
            "nicknameType" => "OWNAC",
            "ownerId" => $this->credentials["ownerId"],
            "ownerType" => $this->credentials["ownerType"],
            "pageAmount" => "6"
        ));
        return $res_asy["body"]["data"];
    }


    public function bankTransferOrft($bankCode, $amount, $accountto, $fromAccountName, $fromAccountNo)
    {
        $authorization = $this->credentials["token"];
        $ibId = $this->credentials["ownerId"];
        $clientRefID = $this->getCurrentCustomTimestamp();
        $endpoint = "";
        $t = "";
        if ($bankCode == "004") {
            $endpoint = $this->ib_gateway . "/api/fundtransferOtherbusiness/inquiryFundTransferOther";
            $t = "FTOT";
        }else{
            $endpoint = $this->ib_gateway . "/api/fundtransferOrftbusiness/inquiryFundTransferOrft";
            $t = "FTOB";
        }
        //$endpoint = $this->ib_gateway . "/api/fundtransferOrftbusiness/inquiryFundTransferOrft";
        
        
        $data = array(
            "favFlag" => "N",
            "fromAccountNo" => $fromAccountNo,
            "fromAccountName" => $fromAccountName,
            "beneficiaryNo" => $accountto,
            "amount" => $amount,
            "totalAmount" => $amount,
            "memo" => "",
            "memoTypeId" => "12",
            // "transType" => "FTOB",
            "transType" => $t,
            "bulk" => "N",
            "bankCode" => $bankCode,
            "feeAmount" => "0.00",
            "scheduleFlag" => "N",
            "transferType" => "Online",
            "effectiveDate" => "",
            "notiEmailNote" => "",
            "smsLang" => "th",
            "lang" => "th",
            "ownerType" => "Company",
            "ownerId" => $ibId,
            "custType" => "IX",
            "attachFileName" => ""
        );

        $headers = array(
            "Content-Type" => "application/json",
            "x-app-id"=>"KBIZWEB",
            "X-Ib-Id" => $ibId,
            "x-re-fresh"=>"N",
            "Authorization" => $authorization,
            "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.43",
            "X-Url" => $this->online_gateway . "/menu/fundtranfer/fundtranfer/fundtranfer-other",
            "X-Verify" => "Y",
            "X-Session-Ibid" => $ibId,
            "X-Request-Id" => $clientRefID,
            "Origin" => $this->online_gateway,
            "Referer" => $this->online_gateway . "/menu/fundtranfer/fundtranfer/fundtranfer-other"
        );

        $response = $this->request("POST", $endpoint, $headers,$data);

        if ($response["code"] === 200) {
            $this->credentials["token"] = $response["header"]["x-session-token"];
            return $response["body"]["data"];
        } else {
            return $response; // Handle error or refresh session if necessary
        }
}

    public function getCurrentCustomTimestamp() {
        // Get current timestamp in milliseconds
        $timestamp = microtime(true) * 1000;
        
        // Convert timestamp to DateTime object to get individual date parts
        $datetime = new DateTime();
        $datetime->setTimestamp(floor($timestamp / 1000)); // Set timestamp in seconds
        
        // Format date parts
        $year = $datetime->format('Y');
        $month = $datetime->format('m');
        $day = $datetime->format('d');
        $hour = $datetime->format('H');
        $minute = $datetime->format('i');
        $second = $datetime->format('s');
        $millisecond = $datetime->format('v'); // Milliseconds
        
        // Concatenate all parts into the desired format
        $customTimestamp = sprintf('%04d%02d%02d%02d%02d%02d%03d', $year, $month, $day, $hour, $minute, $second, $millisecond);
        
        return $customTimestamp;
    }

    public function getTransactionList($account_no = null, $account_type = "SA", $start_date = null, $end_date = null, $pageNo = 1, $rowPerPage = 10)
    {
        if (is_null($start_date) && is_null($end_date)) $start_date = date('d/m/Y');
        if (is_null($end_date)) $end_date = date('d/m/Y');
        $res_tr = $this->request("POST", $this->ib_gateway . "/api/accountsummary/getRecentTransactionList", array(
            "Content-Type" => "application/json",
            "X-Ib-Id" =>  $this->credentials["ownerId"],
            "Authorization" => $this->credentials["token"]
        ), array(
            "acctNo" => $account_no,
            "acctType" => $account_type,
            "custType" => "I",
            "ownerType" => $this->credentials["ownerType"],
            "ownerId" => $this->credentials["ownerId"],
            "pageNo" => $pageNo,
            "rowPerPage" => $rowPerPage,
            "refKey" => "",
            "startDate" => $start_date,
            "endDate" => $end_date
        ));

        return $res_tr["body"]["data"];
    }



    public function getTransactionHistoryMaker($account_no = null, $account_type = "SA", $start_date = null, $end_date = null, $pageNo = 1, $rowPerPage = 10,$accountTo="")
    {
        // {
        //     "approveStatusList": [],
        //     "transStatus": [],
        //     "pageNumber": 1,
        //     "pageAmount": 10,
        //     "custType": "IX",
        //     "lang": "th",
        //     "accountFrom": "1403411880",
        //     "accountFromType": "SA",
        //     "isSeeMore": "Y",
        //     "ownerType": "Company",
        //     "ownerId": "b79d88e0b3642e1e94a67db1283508ee",
        //     "startDate": "",
        //     "endDate": "",
        //     "tranType": "",
        //     "accountTo": "",
        //     "bankCode": ""
        // }

       

        if (is_null($start_date) && is_null($end_date)) $start_date = date('d/m/Y');
        if (is_null($end_date)) $end_date = date('d/m/Y');
        $res_tr = $this->request("POST", $this->ib_gateway . "/api/transactioninquiry/getTransactionHistoryMaker", array(
            "Content-Type" => "application/json",
            "X-Ib-Id" =>  $this->credentials["ownerId"],
            "Authorization" => $this->credentials["token"]
        ),  array(
            "approveStatusList"=>[],
            "transStatus"=>[],
            "pageNumber"=>$pageNo,
            "pageAmount"=>$rowPerPage,
            "custType"=>"IX" ,
            "lang"=>"th",
            "accountFrom"=>$account_no,
            "accountFromType"=>$account_type,
            "isSeeMore"=> "Y",
            "ownerType" => $this->credentials["ownerType"],
            "ownerId" => $this->credentials["ownerId"],
            "startDate" => $start_date,
            "endDate" => $end_date,
            "tranType"=> "",
            "accountTo"=> $accountTo,
            "bankCode"=> ""
        ));


        // $res_tr["body"]["data"]['test'] = $start_date;
        
        return $res_tr["body"]["data"];
    }

    public function getTransactionDetail($account_no = null, $transaction = null)
    {
        $res_tr = $this->request("POST", $this->ib_gateway . "/api/accountsummary/getRecentTransactionDetail", array(
            "Content-Type" => "application/json",
            "X-Ib-Id" =>  $this->credentials["ownerId"],
            "Authorization" => $this->credentials["token"]
        ), array(
            "acctNo" => $account_no,
            "custType" => $transaction["custType"] ?? 'I',
            "debitCreditIndicator" => $transaction["debitCreditIndicator"],
            "origRqUid" => $transaction["origRqUid"],
            "originalSourceId" => $transaction["originalSourceId"],
            "ownerType" => $this->credentials["ownerType"],
            "ownerId" => $this->credentials["ownerId"],
            "transCode" => $transaction["transCode"],
            "transDate" => explode(" ", $transaction["transDate"])[0],
            "transType" => $transaction["transType"],
        ));
        return $res_tr["body"]["data"];
    }


    public function getBlacklistFlag()
    {
        $date = new DateTime();
        $formattedDate = $date->format('YmdHisu');
        $res = $this->request("POST",'https://kbiz.kasikornbank.com/services/api/configuration/getBlacklistFlag',array(
            "Accept" => 'application/json, text/plain, */*',
            "Accept-Encoding" => "gzip, deflate, br",
            "Accept-Language" => "en-US,en;q=0.9",
            "Authorization"=>$this->credentials['token'],
            "Content-Type" => "application/json",
            "Host"=>"kbiz.kasikornbankgroup.com",
            "Origin" => "https://kbiz.kasikornbank.com",
            "Referer" => "https://kbiz.kasikornbank.com/menu/fundtranfer/fundtranfer/fundtranfer-other",
            "Sec-Ch-Ua" => '"Microsoft Edge";v="117", "Not;A=Brand";v="8", "Chromium";v="117"',
            "Sec-Ch-Ua-Mobile" => "?0",
            "Sec-Ch-Ua-Platform" => '"Windows"',
            "Sec-Fetch-Dest" => "empty",
            "Sec-Fetch-Mode" => "cors",
            "Sec-Fetch-Site" => "same-origin",
            "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36 Edg/117.0.2045.31",
            "X-Ib-Id" => $this->credentials['ownerId'],
            "X-Re-Fresh" => "N",
            "X-Request-Id" => $formattedDate,
            "X-Session-Ibid" => $this->credentials['ownerId'],
            "X-Verify" => "Y" ,
        ),"emty");
        return json_encode($res['body']);
    }

}
