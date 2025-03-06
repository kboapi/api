<?php
// include '../db/config.php';
error_reporting(0);
class KrungsriBizOnlineModel {
    private $username;
    private $password;
    private $cookieFile;
    private $URL_LOGIN;
    private $MY_PORTFOLIO_URL;
    private $BASEURL;
    private $DEPOSIT_URL;
    private $GET_STATEMENT_HISTORY_URL;
    private $SMS_URL;
    private $tokenMyAccoun;
    private $tokenTransfer;
    private $cookies;
    private $GetStatementToday;

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
        $this->cookieFile = "./cookies/$username.txt";
        $this->URL_LOGIN = "https://www.krungsribizonline.com/BAY.KOL.Corp.WebSite/Common/Login.aspx";
        $this->MY_PORTFOLIO_URL = "https://www.krungsribizonline.com/BAY.KOL.Corp.WebSite/Pages/MyPortfolio.aspx?d";
        $this->BASEURL = "https://www.krungsribizonline.com";
        $this->DEPOSIT_URL = "https://www.krungsribizonline.com/BAY.KOL.Corp.WebSite/Pages/Deposit/StatementInquiry.aspx?pgno=13";
        $this->GET_STATEMENT_HISTORY_URL = "https://www.krungsribizonline.com/BAY.KOL.Corp.WebSite/Pages/Deposit/StatementInquiryResult.aspx/GetStatementHistory";
        $this->SMS_URL = "https://apikbo.com/api/get_sms2";
        $this->GetStatementToday = "https://www.krungsribizonline.com/BAY.KOL.Corp.WebSite/Pages/MyAccount.aspx/GetStatementToday";
        $this->tokenMyAccoun = null;
        $this->tokenTransfer = null;
        $this->cookies = null;
        $this->payload_transfer = null;

    }

    public function Delete_cookie(){
       return file_get_contents("http://localhost:8080/api/clear_cookie.php?username=".$this->username);
    }


    public function curlRequest($url, $method = "GET", $postData = null, $headers = []) {


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
    
      

        // Set headers and data encoding based on whether the request is JSON
        if ($postData) {
            if ($this->containsJsonContentType($headers)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            }else{
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            }
           
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
           // ตั้งค่าเวลาที่ cURL จะรอให้ response กลับมา
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); // เวลาที่รอให้เชื่อมต่อ
        // curl_setopt($ch, CURLOPT_TIMEOUT, 60); // เวลาที่รอรับ response

        $response = curl_exec($ch);
        // if (curl_errno($ch)) {
        //     throw new Exception(curl_error($ch));
        // }
        curl_close($ch);
        return $response;
    }


    public function curlRequest_load($url, $method = "GET", $postData = null, $headers = []) {

   
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
    

        // Set headers and data encoding based on whether the request is JSON
        if ($postData) {
            if ($this->containsJsonContentType($headers)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            }else{
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            }
           
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
           // ตั้งค่าเวลาที่ cURL จะรอให้ response กลับมา
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); // เวลาที่รอให้เชื่อมต่อ
        // curl_setopt($ch, CURLOPT_TIMEOUT, 60); // เวลาที่รอรับ response

        $response = curl_exec($ch);
        // if (curl_errno($ch)) {
        //     throw new Exception(curl_error($ch));
        // }
        curl_close($ch);
        return $response;
    }



    public function containsJsonContentType($headers) {
        foreach ($headers as $header) {
            if (strpos($header, 'Content-Type:') !== false && strpos($header, 'application/json') !== false) {
                return true;
            }
        }
        return false;
    }

    public function login() {
        // try {
            
            $check_login = $this->MyPortfolio();
            if ($check_login['status'] == 200) {
                return $check_login;
            } else {
                $this->Delete_cookie();
                $loginPageResponse = $this->curlRequest($this->URL_LOGIN);
                $dom = new DOMDocument();
                @$dom->loadHTML($loginPageResponse);
                $xpath = new DOMXPath($dom);
                $formData = [
                    '__EVENTARGUMENT' => '',
                    '__EVENTTARGET' => 'ctl00$cphLoginBox$imgLogin',
                    '__EVENTVALIDATION' => $xpath->query('//input[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value'),
                    '__LASTFOCUS' => '',
                    '__PREVIOUSPAGE' => $xpath->query('//input[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value'),
                    '__VIEWSTATE' => $xpath->query('//input[@id="__VIEWSTATE"]')->item(0)->getAttribute('value'),
                    '__VIEWSTATEENCRYPTED' => '',
                    '__VIEWSTATEGENERATOR' => $xpath->query('//input[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value'),
                    'ctl00$cphLoginBox$hdLogin' => '',
                    'ctl00$cphLoginBox$hdPassword' => $this->password,
                    'ctl00$cphLoginBox$hddLanguage' => 'TH',
                    'ctl00$cphLoginBox$hddPWD' => '',
                    'ctl00$cphLoginBox$txtPasswordSME' => '',
                    'ctl00$cphLoginBox$txtUsernameSME' => $this->username,
                    'ctl00$hddApplicationMode' => 'KBOL',
                    'password' => '',
                    'username' => '',
                ];
                $loginResponse = $this->curlRequest($this->URL_LOGIN,"POST", $formData);

                $dom->loadHTML($loginResponse);
                $loginResponsetitle = $dom->getElementsByTagName('title')->item(0)->nodeValue;
                if ($loginResponsetitle === "Moved Temporarily") {

                    $profile = $this->MyPortfolio();
                    return  $profile;
                   
                } else {
                   //$this->Delete_cookie();
                    file_get_contents("http://localhost:8080/api/clear_cookie.php?username=".$this->username);
                    return [
                        'status' => 404,
                        'msg' => 'error',
                        'errorType' => 'LoginError',
                        'errorMessage' => 'Invalid credentials'
                    ];
                }
            }
    }

    public function MyPortfolio() {
        $myPortfolioPage = $this->curlRequest($this->MY_PORTFOLIO_URL);
        $dom = new DOMDocument();
        @$dom->loadHTML($myPortfolioPage);
        $xpath = new DOMXPath($dom);
        $titles = $xpath->query('//title');
        $title = "";
        for ($i = 0; $i < $titles->length; $i++) {
            if (strpos($titles->item($i)->nodeValue, "Krungsri Biz Online") !== false) {
                $title = "Krungsri Biz Online"; 
                break;
            }
        }
        if ($title == "Krungsri Biz Online") {
            // Extract navigation tokens
            $nodes = $xpath->query('//a[starts-with(@href, "/BAY.KOL.Corp.WebSite")]');
            foreach ($nodes as $node) {
                $href = $node->getAttribute('href');
                if (strpos($href, "/BAY.KOL.Corp.WebSite/Pages/MyAccount.aspx") !== false) {
                    $this->tokenMyAccount = $this->BASEURL . $href;
                } elseif (strpos($href, "/BAY.KOL.Corp.WebSite/Pages/FundTransfer/OtherTransfer.aspx") !== false) {
                    $this->tokenTransfer = $this->BASEURL . $href;
                }
            }
            // Extract user information
            $fullName = trim($xpath->query('//div[contains(@class, "content_left_topbar_title kbol_title")]//div')->item(0)->nodeValue);
            $fullNameArr = explode("\n", $fullName);
            $firstName = trim($fullNameArr[0]);
            $lastName = trim($fullNameArr[1]);
            $balance = trim($xpath->query('//div[@class="amc"]')->item(0)->nodeValue);
            $lastLogin = trim($xpath->query('//*[@id="ctl00_lblShowLastLogin"]')->item(0)->nodeValue);
            preg_match('/accNo="(\d+)"/', $myPortfolioPage, $matches);
            $account_number = isset($matches[1]) ? $matches[1] : null;
            if ($balance == "NOT AVAILABLE") {
                return [
                    'status' => 404,
                    'msg' => 'error',
                    'errorType' => 'maintenance',
                    'errorMessage' => 'API closed for maintenance',
                ];
            }else {
                $data = [
                    'status' => 200,
                    'msg' => 'success',
                    'proxy'=> "" ,
                    'data' => [
                        'username' => $this->username,
                        'account_number' => $account_number,
                        'balance' => $balance,
                        'last_login' => $lastLogin,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'tokenTransfer'=>$this->tokenTransfer,
                        'tokenMyAccount'=>$this->tokenMyAccount
                    ]
                    
                ];
                return $data;
            }
        } else {
            file_get_contents("http://localhost:8080/api/clear_cookie.php?username=".$this->username);
            return [
                'status' => 404,
                'msg' => 'error',
                'errorType' => 'LoginError',
                'errorMessage' => 'MyPortfolio Invalid credentials',
            ];
        }
    }
    


    public function getStatement($startDate = null, $endDate = null, $next = null) {
        try {
            // Default the start and end dates to today if not provided
            $date = new DateTime();
            if ($startDate === null) {
                $startDate = $date->format('Y-m-d');
            }
            if ($endDate === null) {
                $endDate = $date->format('Y-m-d');
            }
    
            // Fetch the statement inquiry page to get necessary data
            $statementPageResponse = $this->curlRequest($this->DEPOSIT_URL, "GET");
            $dom = new DOMDocument();
            @$dom->loadHTML($statementPageResponse);
            $xpath = new DOMXPath($dom);
    
            // Extract required values from the page
            $viewState = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
            $viewStateGenerator = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
            $previousPage = $xpath->query('//*[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value');
            $eventValidation = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');
            $accountDropdown = $xpath->query('//*[@id="ctl00_cphSectionData_ddlAccount"]/option[last()]')->item(0)->getAttribute('value');

            $payload = [
                'ctl00$smMain' => 'ctl00$cphSectionData$udpButton|ctl00$cphSectionData$btnInquiry',
                '__EVENTTARGET' => '',
                '__EVENTARGUMENT' => '',
                '__VIEWSTATE' => $viewState,
                '__VIEWSTATEGENERATOR' => $viewStateGenerator,
                '__VIEWSTATEENCRYPTED' => '',
                '__PREVIOUSPAGE' => $previousPage,
                '__EVENTVALIDATION' => $eventValidation,
                'ctl00$hddNoAcc' => '',
                'ctl00$hddMainAccIsCreditCard' => '',
                'ctl00$bannerTop$hdTransactionType' => '',
                'ctl00$bannerTop$hdCampaignCode' => '',
                'ctl00$bannerTop$hdCampaignTxnType' => '',
                'ctl00$bannerTop$hdCampaignMutualFundType' => '',
                'ctl00$bannerTop$hdCampaignTransferType' => '',
                'ctl00$bannerTop$hdAccNo' => '',
                'ctl00$bannerTop$hdBillerId' => '',
                'ctl00$bannerTop$hdUrlRedirect' => '',
                'ctl00$bannerTop$hdAmount' => '',
                'ctl00$bannerTop$hdTxnIsSuccess' => '',
                'ctl00$bannerTop$hdBillerCategory' => '',
                'ctl00$bannerTop$hdBillerName' => '',
                'ctl00$bannerTop$hdAJAXData' => '',
                'ctl00$hddIsLoadComplete' => false,
                'ctl00$hdnCurrentPageQuickMenu' => '',
                'ctl00$hdnPageIndexQuickMenuLoaded' => '',
                'ctl00$cphSectionData$ddlAccount' => $accountDropdown,
                'ctl00$cphSectionData$dpStart' => date('d/m/Y', strtotime($startDate)),
                'ctl00$cphSectionData$dpEnd' => date('d/m/Y', strtotime($endDate)),
                'ctl00$hddHasSess' => '',
                '__ASYNCPOST' => 'true',
                'ctl00$cphSectionData$btnInquiry' => 'แสดงรายการ'
            ];

            //return $payload;
            // Send POST request to get statement history
            $statementPostResponse = $this->curlRequest($this->DEPOSIT_URL,"POST",$payload,
            ["Content-Type: application/x-www-form-urlencoded",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.112 Safari/537.36"]);
            preg_match('/%2f[^|]+(?=\|)/', $statementPostResponse, $matches);
            $historyUrl = isset($matches[0]) ? $this->BASEURL . urldecode($matches[0]) : '';
            $this->curlRequest($historyUrl);
            $statementHistoryResponse = $this->curlRequest($this->GET_STATEMENT_HISTORY_URL, "POST", [
                'pageIndex' => 1,
                'pageoffset' => $next,
                'language' => 'TH',
                'jsonparam' => json_encode([
                    'AccNo' => null,
                    'AccType' => 1,
                    'FromRequest' => $startDate . 'T00:00:00',
                    'ToRequest' => $endDate . 'T00:00:00',
                    'CustId' => null,
                    'PagingOffset' => null,
                    'PageSize' => 0,
                    'SortBy' => null,
                ]),
            ],[
            "Content-Type: application/json; charset=UTF-8",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.112 Safari/537.36"
            ]);
            $data = json_decode($statementHistoryResponse, true);
            $data_decode = json_decode($data['d'],true);
            return [
                'status' => 200,
                'msg' => 'success',
                'proxy'=> "" ,
                'data' => $data_decode
            ];
        } catch (Exception $error) {
            echo "Error occurred: " . $error->getMessage();
            return $this->getStatement($startDate, $endDate, $next);
        }
    }

    public function getStatementToday() {
            $statementPageResponse = $this->curlRequest($this->tokenMyAccount, "GET");
            $dom = new DOMDocument();
            @$dom->loadHTML($statementPageResponse);
            $xpath = new DOMXPath($dom);
            $title = trim($xpath->query('//title')->item(0)->nodeValue);
            if ($title == "Krungsri Biz Online") {
                $GetStatementToday = $this->curlRequest($this->GetStatementToday, "POST", [
                    'pageIndex' => "1",
                    'pageoffset' => ""
                ],[
                "Content-Type: application/json; charset=UTF-8",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.112 Safari/537.36"
                ]);
                $data = json_decode($GetStatementToday, true);
                $data_decode = json_decode($data['d'],true);
                return [
                    'status' => 200,
                    'msg' => 'success',
                    'proxy'=> "",
                    'data' => $data_decode
                ];
            }else{
               
                file_get_contents("http://localhost:8080/api/clear_cookie.php?username=".$this->username);
                return [
                    'status' => 404,
                    'msg' => 'error',
                    'errorType' => 'GetStatementError',
                    'errorMessage' => 'GetStatementError',
                ];

            }
    }


    public function transfer($AccTo=null, $bankcode=null, $amount=null,$token_transfer=null){

        if($token_transfer){
            $this->tokenTransfer = $token_transfer;
        }
        $doc = new DOMDocument();
        $getTransfer = $this->curlRequest($this->tokenTransfer,'GET');
        $doc->loadHTML($getTransfer);
        $xpath = new DOMXPath($doc);
        $loginResponsetitle = $xpath->query('//title')->item(0)->nodeValue;
        if (trim($loginResponsetitle) == "Krungsri Biz Online") {
            $__VIEWSTATE = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
            $__VIEWSTATEGENERATOR = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
            $__PREVIOUSPAGE = $xpath->query('//*[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value');
            $__EVENTVALIDATION = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');  
            $hfFromAccNo = $xpath->query('//input[@name="ctl00$cphSectionButton$hfFromAccNo"]')->item(0)->getAttribute("value");
            $payload = array(
                'ctl00$smMain' => 'ctl00$smMain|ctl00$cphSectionData$btnSubmit',
                "__EVENTTARGET" => "",
                "__EVENTARGUMENT" => "",
                "__LASTFOCUS" => "",
                "__VIEWSTATE" => $__VIEWSTATE,
                "__VIEWSTATEGENERATOR" => $__VIEWSTATEGENERATOR,
                "__VIEWSTATEENCRYPTED" => "",
                "__PREVIOUSPAGE" => $__PREVIOUSPAGE,
                "__EVENTVALIDATION" => $__EVENTVALIDATION,
                'ctl00$hddNoAcc' => "",
                'ctl00$hddMainAccIsCreditCard' => "",
                'ctl00$bannerTop$hdTransactionType' => "",
                'ctl00$bannerTop$hdCampaignCode' => "",
                'ctl00$bannerTop$hdCampaignTxnType' => "",
                'ctl00$bannerTop$hdCampaignMutualFundType' => "",
                'ctl00$bannerTop$hdCampaignTransferType' => "",
                'ctl00$bannerTop$hdAccNo' => "",
                'ctl00$bannerTop$hdBillerId' => "",
                'ctl00$bannerTop$hdUrlRedirect' => "",
                'ctl00$bannerTop$hdAmount' => "",
                'ctl00$bannerTop$hdTxnIsSuccess' => "",
                'ctl00$bannerTop$hdBillerCategory' => "",
                'ctl00$bannerTop$hdBillerName' => "",
                'ctl00$bannerTop$hdAJAXData' => "",
                'ctl00$hddIsLoadComplete' => false,
                'ctl00$hdnCurrentPageQuickMenu' => "",
                'ctl00$hdnPageIndexQuickMenuLoaded' => "",
                'ctl00$cphSectionData$ddlBanking' => $bankcode,
                'ctl00$cphSectionData$txtAccTo' => $AccTo,
                'ctl00$cphSectionData$txtAccToP2P' => "",
                'ctl00$cphSectionData$txtAmountTransfer' => $amount,
                'ctl00$cphSectionData$ddlFixedType' => "",
                'ctl00$cphSectionData$txtOtherReason' => "",
                'ctl00$cphSectionData$scheduleType' => "now",
                'ctl00$cphSectionData$txtPaymentDate_Once' => "",
                'ctl00$cphSectionData$ddlRecurring' => "",
                'ctl00$cphSectionData$txtRecurringDateStart' => "",
                'ctl00$cphSectionData$txtRecurringDateEnd' => "",
                'ctl00$cphSectionData$alertType' => "yes",
                'ctl00$cphSectionData$notify_receiver' => "0",
                'ctl00$cphSectionData$txtEmailNotifyTo' => "",
                'ctl00$cphSectionData$txtEmailNotifyToName' => "",
                'ctl00$cphSectionData$txtEmailNotifyToRemark' => "",
                'ctl00$cphSectionData$txtSMSNotifyToMobileNo' => "",
                'ctl00$cphSectionData$txtSMSNotifyToName' => "",
                'ctl00$cphSectionData$txtMemo' => "",
                'ctl00$cphSectionData$hdScheduleId' => "",
                'ctl00$cphSectionData$hdScheduleUI' => "0",
                'ctl00$cphSectionData$hdTransactionCode' => "",
                'ctl00$cphSectionButton$hfDefault' => 'ctl00_cphSectionData_rptAccFrom_ctl00_pnlFromAcc',
                'ctl00$cphSectionButton$hfToDefault' => 'ctl00_cphSectionData_pnlToNewAcc_cate',
                'ctl00$cphSectionButton$hfMainAccount' => 'ctl00_cphSectionData_rptAccFrom_ctl00_pnlFromAcc',
                'ctl00$cphSectionButton$hfToAccount' => 'ctl00_cphSectionData_pnlToNewAcc_cate',
                'ctl00$cphSectionButton$hfFromAccNo' => $hfFromAccNo,
                'ctl00$cphSectionButton$hfToAccNo' => $AccTo,
                'ctl00$cphSectionButton$hfToCode' => $bankcode,
                'ctl00$cphSectionButton$hfEmail' => '0',
                'ctl00$cphSectionButton$hfSMS' => '0',
                'ctl00$cphSectionButton$hfOthereasonID' => "",
                'ctl00$cphSectionButton$hfCannotAccess' => "",
                'ctl00$cphSectionButton$hfP2P' => "",
                'ctl00$cphSectionButton$hdnLanguageUsed' => "TH",
                'ctl00$hddHasSess' => "",
                '__ASYNCPOST' => true,
                'ctl00$cphSectionData$btnSubmit' => "ดำเนินการ"
            );
            
            $postTransfer = $this->curlRequest($this->tokenTransfer,'POST',$payload,[
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.112 Safari/537.36",
            ]);
            $doc = new DOMDocument();
            $doc->loadHTML($postTransfer);
            $xpath = new DOMXPath($doc);

            $confirm_url = $this->BASEURL . ($ConfirmTransfer = array_values(array_filter(array_map(function($anchor) { return $anchor->getAttribute('href'); }, iterator_to_array($doc->getElementsByTagName('a'))), function($href) { return substr($href, 0, 1) === '/'; }))[0] ?? '');

            $confirmtransfer_get = $this->curlRequest($confirm_url);

            $doc = new DOMDocument();
            $doc->loadHTML($confirmtransfer_get);
            $xpath = new DOMXPath($doc);
            $ref = preg_match('/<div class="input_input_half">\s*(\d+)\s*<\/div>/', $confirmtransfer_get, $matches) ? $matches[1] : "";
            $__VIEWSTATE = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
            $__VIEWSTATEGENERATOR = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
            $__PREVIOUSPAGE = $xpath->query('//*[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value');
            $__EVENTVALIDATION = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');
            if (trim($ref) == "") {

                $__VIEWSTATE = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
                $__VIEWSTATEGENERATOR = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
                $__PREVIOUSPAGE = $xpath->query('//*[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value');
                $__EVENTVALIDATION = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');  
                $hfFromAccNo = $xpath->query('//input[@name="ctl00$cphSectionButton$hfFromAccNo"]')->item(0)->getAttribute("value");
                $payload = array(
                    'ctl00$smMain' => 'ctl00$smMain|ctl00$cphSectionData$btnSubmit',
                    "__EVENTTARGET" => "",
                    "__EVENTARGUMENT" => "",
                    "__LASTFOCUS" => "",
                    "__VIEWSTATE" => $__VIEWSTATE,
                    "__VIEWSTATEGENERATOR" => $__VIEWSTATEGENERATOR,
                    "__VIEWSTATEENCRYPTED" => "",
                    "__PREVIOUSPAGE" => $__PREVIOUSPAGE,
                    "__EVENTVALIDATION" => $__EVENTVALIDATION,
                    'ctl00$hddNoAcc' => "",
                    'ctl00$hddMainAccIsCreditCard' => "",
                    'ctl00$bannerTop$hdTransactionType' => "",
                    'ctl00$bannerTop$hdCampaignCode' => "",
                    'ctl00$bannerTop$hdCampaignTxnType' => "",
                    'ctl00$bannerTop$hdCampaignMutualFundType' => "",
                    'ctl00$bannerTop$hdCampaignTransferType' => "",
                    'ctl00$bannerTop$hdAccNo' => "",
                    'ctl00$bannerTop$hdBillerId' => "",
                    'ctl00$bannerTop$hdUrlRedirect' => "",
                    'ctl00$bannerTop$hdAmount' => "",
                    'ctl00$bannerTop$hdTxnIsSuccess' => "",
                    'ctl00$bannerTop$hdBillerCategory' => "",
                    'ctl00$bannerTop$hdBillerName' => "",
                    'ctl00$bannerTop$hdAJAXData' => "",
                    'ctl00$hddIsLoadComplete' => false,
                    'ctl00$hdnCurrentPageQuickMenu' => "",
                    'ctl00$hdnPageIndexQuickMenuLoaded' => "",
                    'ctl00$cphSectionData$ddlBanking' => $bankcode,
                    'ctl00$cphSectionData$txtAccTo' => $AccTo,
                    'ctl00$cphSectionData$txtAccToP2P' => "",
                    'ctl00$cphSectionData$txtAmountTransfer' => $amount,
                    'ctl00$cphSectionData$ddlFixedType' => "",
                    'ctl00$cphSectionData$txtOtherReason' => "",
                    'ctl00$cphSectionData$scheduleType' => "now",
                    'ctl00$cphSectionData$txtPaymentDate_Once' => "",
                    'ctl00$cphSectionData$ddlRecurring' => "",
                    'ctl00$cphSectionData$txtRecurringDateStart' => "",
                    'ctl00$cphSectionData$txtRecurringDateEnd' => "",
                    'ctl00$cphSectionData$alertType' => "yes",
                    'ctl00$cphSectionData$notify_receiver' => "0",
                    'ctl00$cphSectionData$txtEmailNotifyTo' => "",
                    'ctl00$cphSectionData$txtEmailNotifyToName' => "",
                    'ctl00$cphSectionData$txtEmailNotifyToRemark' => "",
                    'ctl00$cphSectionData$txtSMSNotifyToMobileNo' => "",
                    'ctl00$cphSectionData$txtSMSNotifyToName' => "",
                    'ctl00$cphSectionData$txtMemo' => "",
                    'ctl00$cphSectionData$hdScheduleId' => "",
                    'ctl00$cphSectionData$hdScheduleUI' => "0",
                    'ctl00$cphSectionData$hdTransactionCode' => "",
                    'ctl00$cphSectionButton$hfDefault' => 'ctl00_cphSectionData_rptAccFrom_ctl00_pnlFromAcc',
                    'ctl00$cphSectionButton$hfToDefault' => 'ctl00_cphSectionData_pnlToNewAcc_cate',
                    'ctl00$cphSectionButton$hfMainAccount' => 'ctl00_cphSectionData_rptAccFrom_ctl00_pnlFromAcc',
                    'ctl00$cphSectionButton$hfToAccount' => 'ctl00_cphSectionData_pnlToNewAcc_cate',
                    'ctl00$cphSectionButton$hfFromAccNo' => $hfFromAccNo,
                    'ctl00$cphSectionButton$hfToAccNo' => $AccTo,
                    'ctl00$cphSectionButton$hfToCode' => $bankcode,
                    'ctl00$cphSectionButton$hfEmail' => '0',
                    'ctl00$cphSectionButton$hfSMS' => '0',
                    'ctl00$cphSectionButton$hfOthereasonID' => "",
                    'ctl00$cphSectionButton$hfCannotAccess' => "",
                    'ctl00$cphSectionButton$hfP2P' => "",
                    'ctl00$cphSectionButton$hdnLanguageUsed' => "TH",
                    'ctl00$hddHasSess' => "",
                    '__ASYNCPOST' => true,
                    'ctl00$cphSectionData$btnSubmit' => "ดำเนินการ"
                );
                
                $postTransfer = $this->curlRequest($this->tokenTransfer,'POST',$payload,[
                    "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.112 Safari/537.36",
                ]);
                $doc = new DOMDocument();
                $doc->loadHTML($postTransfer);
                $xpath = new DOMXPath($doc);
    
                $confirm_url = $this->BASEURL . ($ConfirmTransfer = array_values(array_filter(array_map(function($anchor) { return $anchor->getAttribute('href'); }, iterator_to_array($doc->getElementsByTagName('a'))), function($href) { return substr($href, 0, 1) === '/'; }))[0] ?? '');
    
                $confirmtransfer_get = $this->curlRequest($confirm_url);
    
                $doc = new DOMDocument();
                $doc->loadHTML($confirmtransfer_get);
                $xpath = new DOMXPath($doc);
                $ref = preg_match('/<div class="input_input_half">\s*(\d+)\s*<\/div>/', $confirmtransfer_get, $matches) ? $matches[1] : "";
                $__VIEWSTATE = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
                $__VIEWSTATEGENERATOR = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
                $__PREVIOUSPAGE = $xpath->query('//*[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value');
                $__EVENTVALIDATION = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');

                return array(
                    'status' => 403,
                    'msg' => 'error',
                    'errorType' => 'OTP',
                    'errorMessage' => "ref : ไม่มาลอง reload ใหม่".$ref
                );
            }

            $this->payload_transfer['link'] = $confirm_url;
            $this->payload_transfer['data'] = array(
                'ctl00$smMain' => 'ctl00$cphSectionData$OTPBox1$udpOTPBox|ctl00$cphSectionData$OTPBox1$btnConfirm',
                '__EVENTTARGET' => 'ctl00$cphSectionData$OTPBox1$btnConfirm',
                '__EVENTARGUMENT' => '',
                '__VIEWSTATE' => $__VIEWSTATE,
                '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
                '__VIEWSTATEENCRYPTED' => '',
                '__PREVIOUSPAGE' => $__PREVIOUSPAGE,
                '__EVENTVALIDATION' => $__EVENTVALIDATION,
                'ctl00$hddNoAcc' => '',
                'ctl00$hddMainAccIsCreditCard' => '',
                'ctl00$bannerTop$hdTransactionType' => '',
                'ctl00$bannerTop$hdCampaignCode' => '',
                'ctl00$bannerTop$hdCampaignTxnType' => '',
                'ctl00$bannerTop$hdCampaignMutualFundType' => '',
                'ctl00$bannerTop$hdCampaignTransferType' => '',
                'ctl00$bannerTop$hdAccNo' => '',
                'ctl00$bannerTop$hdBillerId' => '',
                'ctl00$bannerTop$hdUrlRedirect' => '',
                'ctl00$bannerTop$hdAmount' => '',
                'ctl00$bannerTop$hdTxnIsSuccess' => '',
                'ctl00$bannerTop$hdBillerCategory' => '',
                'ctl00$bannerTop$hdBillerName' => '',
                'ctl00$bannerTop$hdAJAXData' => '',
                'ctl00$hddIsLoadComplete' => false,
                'ctl00$hdnCurrentPageQuickMenu' => '',
                'ctl00$hdnPageIndexQuickMenuLoaded' => '',
                'ctl00$cphSectionData$OTPBox1$Password2' => '',
                'ctl00$cphSectionData$OTPBox1$txtTemp' => '',
                'ctl00$cphSectionData$OTPBox1$hddOTPPassword' => '',
                'ctl00$cphSectionData$OTPBox1$txtOTPPassword' => '',
                'ctl00$hddHasSess' => '',
                '__ASYNCPOST' => true,
            );
            return array(
                'status' => 200,
                'msg' => 'ส่ง otp สำเร็จ',
                'ref' => $ref
            );

        }            
    }


    public function transfer_load($AccTo=null, $bankcode=null, $amount=null,$token_transfer=null){

        if($token_transfer){
            $this->tokenTransfer = $token_transfer;
        }
        $doc = new DOMDocument();
        $getTransfer = $this->curlRequest_load($this->tokenTransfer,'GET');
        $doc->loadHTML($getTransfer);
        $xpath = new DOMXPath($doc);
        $loginResponsetitle = $xpath->query('//title')->item(0)->nodeValue;
        if (trim($loginResponsetitle) == "Krungsri Biz Online") {
            $__VIEWSTATE = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
            $__VIEWSTATEGENERATOR = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
            $__PREVIOUSPAGE = $xpath->query('//*[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value');
            $__EVENTVALIDATION = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');  
            $hfFromAccNo = $xpath->query('//input[@name="ctl00$cphSectionButton$hfFromAccNo"]')->item(0)->getAttribute("value");
            $payload = array(
                'ctl00$smMain' => 'ctl00$smMain|ctl00$cphSectionData$btnSubmit',
                "__EVENTTARGET" => "",
                "__EVENTARGUMENT" => "",
                "__LASTFOCUS" => "",
                "__VIEWSTATE" => $__VIEWSTATE,
                "__VIEWSTATEGENERATOR" => $__VIEWSTATEGENERATOR,
                "__VIEWSTATEENCRYPTED" => "",
                "__PREVIOUSPAGE" => $__PREVIOUSPAGE,
                "__EVENTVALIDATION" => $__EVENTVALIDATION,
                'ctl00$hddNoAcc' => "",
                'ctl00$hddMainAccIsCreditCard' => "",
                'ctl00$bannerTop$hdTransactionType' => "",
                'ctl00$bannerTop$hdCampaignCode' => "",
                'ctl00$bannerTop$hdCampaignTxnType' => "",
                'ctl00$bannerTop$hdCampaignMutualFundType' => "",
                'ctl00$bannerTop$hdCampaignTransferType' => "",
                'ctl00$bannerTop$hdAccNo' => "",
                'ctl00$bannerTop$hdBillerId' => "",
                'ctl00$bannerTop$hdUrlRedirect' => "",
                'ctl00$bannerTop$hdAmount' => "",
                'ctl00$bannerTop$hdTxnIsSuccess' => "",
                'ctl00$bannerTop$hdBillerCategory' => "",
                'ctl00$bannerTop$hdBillerName' => "",
                'ctl00$bannerTop$hdAJAXData' => "",
                'ctl00$hddIsLoadComplete' => false,
                'ctl00$hdnCurrentPageQuickMenu' => "",
                'ctl00$hdnPageIndexQuickMenuLoaded' => "",
                'ctl00$cphSectionData$ddlBanking' => $bankcode,
                'ctl00$cphSectionData$txtAccTo' => $AccTo,
                'ctl00$cphSectionData$txtAccToP2P' => "",
                'ctl00$cphSectionData$txtAmountTransfer' => $amount,
                'ctl00$cphSectionData$ddlFixedType' => "",
                'ctl00$cphSectionData$txtOtherReason' => "",
                'ctl00$cphSectionData$scheduleType' => "now",
                'ctl00$cphSectionData$txtPaymentDate_Once' => "",
                'ctl00$cphSectionData$ddlRecurring' => "",
                'ctl00$cphSectionData$txtRecurringDateStart' => "",
                'ctl00$cphSectionData$txtRecurringDateEnd' => "",
                'ctl00$cphSectionData$alertType' => "yes",
                'ctl00$cphSectionData$notify_receiver' => "0",
                'ctl00$cphSectionData$txtEmailNotifyTo' => "",
                'ctl00$cphSectionData$txtEmailNotifyToName' => "",
                'ctl00$cphSectionData$txtEmailNotifyToRemark' => "",
                'ctl00$cphSectionData$txtSMSNotifyToMobileNo' => "",
                'ctl00$cphSectionData$txtSMSNotifyToName' => "",
                'ctl00$cphSectionData$txtMemo' => "",
                'ctl00$cphSectionData$hdScheduleId' => "",
                'ctl00$cphSectionData$hdScheduleUI' => "0",
                'ctl00$cphSectionData$hdTransactionCode' => "",
                'ctl00$cphSectionButton$hfDefault' => 'ctl00_cphSectionData_rptAccFrom_ctl00_pnlFromAcc',
                'ctl00$cphSectionButton$hfToDefault' => 'ctl00_cphSectionData_pnlToNewAcc_cate',
                'ctl00$cphSectionButton$hfMainAccount' => 'ctl00_cphSectionData_rptAccFrom_ctl00_pnlFromAcc',
                'ctl00$cphSectionButton$hfToAccount' => 'ctl00_cphSectionData_pnlToNewAcc_cate',
                'ctl00$cphSectionButton$hfFromAccNo' => $hfFromAccNo,
                'ctl00$cphSectionButton$hfToAccNo' => $AccTo,
                'ctl00$cphSectionButton$hfToCode' => $bankcode,
                'ctl00$cphSectionButton$hfEmail' => '0',
                'ctl00$cphSectionButton$hfSMS' => '0',
                'ctl00$cphSectionButton$hfOthereasonID' => "",
                'ctl00$cphSectionButton$hfCannotAccess' => "",
                'ctl00$cphSectionButton$hfP2P' => "",
                'ctl00$cphSectionButton$hdnLanguageUsed' => "TH",
                'ctl00$hddHasSess' => "",
                '__ASYNCPOST' => true,
                'ctl00$cphSectionData$btnSubmit' => "ดำเนินการ"
            );
            
            $postTransfer = $this->curlRequest_load($this->tokenTransfer,'POST',$payload,[
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.112 Safari/537.36",
            ]);
            $doc = new DOMDocument();
            $doc->loadHTML($postTransfer);
            $xpath = new DOMXPath($doc);

            $confirm_url = $this->BASEURL . ($ConfirmTransfer = array_values(array_filter(array_map(function($anchor) { return $anchor->getAttribute('href'); }, iterator_to_array($doc->getElementsByTagName('a'))), function($href) { return substr($href, 0, 1) === '/'; }))[0] ?? '');

            $confirmtransfer_get = $this->curlRequest_load($confirm_url);

            $doc = new DOMDocument();
            $doc->loadHTML($confirmtransfer_get);
            $xpath = new DOMXPath($doc);
            $ref = preg_match('/<div class="input_input_half">\s*(\d+)\s*<\/div>/', $confirmtransfer_get, $matches) ? $matches[1] : "";
            $__VIEWSTATE = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
            $__VIEWSTATEGENERATOR = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
            $__PREVIOUSPAGE = $xpath->query('//*[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value');
            $__EVENTVALIDATION = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');
            if (trim($ref) == "") {

                $__VIEWSTATE = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
                $__VIEWSTATEGENERATOR = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
                $__PREVIOUSPAGE = $xpath->query('//*[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value');
                $__EVENTVALIDATION = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');  
                $hfFromAccNo = $xpath->query('//input[@name="ctl00$cphSectionButton$hfFromAccNo"]')->item(0)->getAttribute("value");
                $payload = array(
                    'ctl00$smMain' => 'ctl00$smMain|ctl00$cphSectionData$btnSubmit',
                    "__EVENTTARGET" => "",
                    "__EVENTARGUMENT" => "",
                    "__LASTFOCUS" => "",
                    "__VIEWSTATE" => $__VIEWSTATE,
                    "__VIEWSTATEGENERATOR" => $__VIEWSTATEGENERATOR,
                    "__VIEWSTATEENCRYPTED" => "",
                    "__PREVIOUSPAGE" => $__PREVIOUSPAGE,
                    "__EVENTVALIDATION" => $__EVENTVALIDATION,
                    'ctl00$hddNoAcc' => "",
                    'ctl00$hddMainAccIsCreditCard' => "",
                    'ctl00$bannerTop$hdTransactionType' => "",
                    'ctl00$bannerTop$hdCampaignCode' => "",
                    'ctl00$bannerTop$hdCampaignTxnType' => "",
                    'ctl00$bannerTop$hdCampaignMutualFundType' => "",
                    'ctl00$bannerTop$hdCampaignTransferType' => "",
                    'ctl00$bannerTop$hdAccNo' => "",
                    'ctl00$bannerTop$hdBillerId' => "",
                    'ctl00$bannerTop$hdUrlRedirect' => "",
                    'ctl00$bannerTop$hdAmount' => "",
                    'ctl00$bannerTop$hdTxnIsSuccess' => "",
                    'ctl00$bannerTop$hdBillerCategory' => "",
                    'ctl00$bannerTop$hdBillerName' => "",
                    'ctl00$bannerTop$hdAJAXData' => "",
                    'ctl00$hddIsLoadComplete' => false,
                    'ctl00$hdnCurrentPageQuickMenu' => "",
                    'ctl00$hdnPageIndexQuickMenuLoaded' => "",
                    'ctl00$cphSectionData$ddlBanking' => $bankcode,
                    'ctl00$cphSectionData$txtAccTo' => $AccTo,
                    'ctl00$cphSectionData$txtAccToP2P' => "",
                    'ctl00$cphSectionData$txtAmountTransfer' => $amount,
                    'ctl00$cphSectionData$ddlFixedType' => "",
                    'ctl00$cphSectionData$txtOtherReason' => "",
                    'ctl00$cphSectionData$scheduleType' => "now",
                    'ctl00$cphSectionData$txtPaymentDate_Once' => "",
                    'ctl00$cphSectionData$ddlRecurring' => "",
                    'ctl00$cphSectionData$txtRecurringDateStart' => "",
                    'ctl00$cphSectionData$txtRecurringDateEnd' => "",
                    'ctl00$cphSectionData$alertType' => "yes",
                    'ctl00$cphSectionData$notify_receiver' => "0",
                    'ctl00$cphSectionData$txtEmailNotifyTo' => "",
                    'ctl00$cphSectionData$txtEmailNotifyToName' => "",
                    'ctl00$cphSectionData$txtEmailNotifyToRemark' => "",
                    'ctl00$cphSectionData$txtSMSNotifyToMobileNo' => "",
                    'ctl00$cphSectionData$txtSMSNotifyToName' => "",
                    'ctl00$cphSectionData$txtMemo' => "",
                    'ctl00$cphSectionData$hdScheduleId' => "",
                    'ctl00$cphSectionData$hdScheduleUI' => "0",
                    'ctl00$cphSectionData$hdTransactionCode' => "",
                    'ctl00$cphSectionButton$hfDefault' => 'ctl00_cphSectionData_rptAccFrom_ctl00_pnlFromAcc',
                    'ctl00$cphSectionButton$hfToDefault' => 'ctl00_cphSectionData_pnlToNewAcc_cate',
                    'ctl00$cphSectionButton$hfMainAccount' => 'ctl00_cphSectionData_rptAccFrom_ctl00_pnlFromAcc',
                    'ctl00$cphSectionButton$hfToAccount' => 'ctl00_cphSectionData_pnlToNewAcc_cate',
                    'ctl00$cphSectionButton$hfFromAccNo' => $hfFromAccNo,
                    'ctl00$cphSectionButton$hfToAccNo' => $AccTo,
                    'ctl00$cphSectionButton$hfToCode' => $bankcode,
                    'ctl00$cphSectionButton$hfEmail' => '0',
                    'ctl00$cphSectionButton$hfSMS' => '0',
                    'ctl00$cphSectionButton$hfOthereasonID' => "",
                    'ctl00$cphSectionButton$hfCannotAccess' => "",
                    'ctl00$cphSectionButton$hfP2P' => "",
                    'ctl00$cphSectionButton$hdnLanguageUsed' => "TH",
                    'ctl00$hddHasSess' => "",
                    '__ASYNCPOST' => true,
                    'ctl00$cphSectionData$btnSubmit' => "ดำเนินการ"
                );
                
                $postTransfer = $this->curlRequest_load($this->tokenTransfer,'POST',$payload,[
                    "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.112 Safari/537.36",
                ]);
                $doc = new DOMDocument();
                $doc->loadHTML($postTransfer);
                $xpath = new DOMXPath($doc);
    
                $confirm_url = $this->BASEURL . ($ConfirmTransfer = array_values(array_filter(array_map(function($anchor) { return $anchor->getAttribute('href'); }, iterator_to_array($doc->getElementsByTagName('a'))), function($href) { return substr($href, 0, 1) === '/'; }))[0] ?? '');
    
                $confirmtransfer_get = $this->curlRequest_load($confirm_url);
    
                $doc = new DOMDocument();
                $doc->loadHTML($confirmtransfer_get);
                $xpath = new DOMXPath($doc);
                $ref = preg_match('/<div class="input_input_half">\s*(\d+)\s*<\/div>/', $confirmtransfer_get, $matches) ? $matches[1] : "";
                $__VIEWSTATE = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
                $__VIEWSTATEGENERATOR = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
                $__PREVIOUSPAGE = $xpath->query('//*[@id="__PREVIOUSPAGE"]')->item(0)->getAttribute('value');
                $__EVENTVALIDATION = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');

                return array(
                    'status' => 403,
                    'msg' => 'error',
                    'errorType' => 'OTP',
                    'errorMessage' => "ref : ไม่มาลอง reload ใหม่".$ref
                );
            }

            $this->payload_transfer['link'] = $confirm_url;
            $this->payload_transfer['data'] = array(
                'ctl00$smMain' => 'ctl00$cphSectionData$OTPBox1$udpOTPBox|ctl00$cphSectionData$OTPBox1$btnConfirm',
                '__EVENTTARGET' => 'ctl00$cphSectionData$OTPBox1$btnConfirm',
                '__EVENTARGUMENT' => '',
                '__VIEWSTATE' => $__VIEWSTATE,
                '__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
                '__VIEWSTATEENCRYPTED' => '',
                '__PREVIOUSPAGE' => $__PREVIOUSPAGE,
                '__EVENTVALIDATION' => $__EVENTVALIDATION,
                'ctl00$hddNoAcc' => '',
                'ctl00$hddMainAccIsCreditCard' => '',
                'ctl00$bannerTop$hdTransactionType' => '',
                'ctl00$bannerTop$hdCampaignCode' => '',
                'ctl00$bannerTop$hdCampaignTxnType' => '',
                'ctl00$bannerTop$hdCampaignMutualFundType' => '',
                'ctl00$bannerTop$hdCampaignTransferType' => '',
                'ctl00$bannerTop$hdAccNo' => '',
                'ctl00$bannerTop$hdBillerId' => '',
                'ctl00$bannerTop$hdUrlRedirect' => '',
                'ctl00$bannerTop$hdAmount' => '',
                'ctl00$bannerTop$hdTxnIsSuccess' => '',
                'ctl00$bannerTop$hdBillerCategory' => '',
                'ctl00$bannerTop$hdBillerName' => '',
                'ctl00$bannerTop$hdAJAXData' => '',
                'ctl00$hddIsLoadComplete' => false,
                'ctl00$hdnCurrentPageQuickMenu' => '',
                'ctl00$hdnPageIndexQuickMenuLoaded' => '',
                'ctl00$cphSectionData$OTPBox1$Password2' => '',
                'ctl00$cphSectionData$OTPBox1$txtTemp' => '',
                'ctl00$cphSectionData$OTPBox1$hddOTPPassword' => '',
                'ctl00$cphSectionData$OTPBox1$txtOTPPassword' => '',
                'ctl00$hddHasSess' => '',
                '__ASYNCPOST' => true,
            );
            return array(
                'status' => 200,
                'msg' => 'ส่ง otp สำเร็จ',
                'ref' => $ref
            );

        }            
    }

    public function getSmsOtp($ref,$AccTo) {
        $startTime = time();
        $statusOtp = null;
        while (true) {
            
            $response = $this->curlRequest($this->SMS_URL . "?ref=" .$ref."&phone=".$AccTo);
            if ($response == 'null') {
               
            }else{
                $sms = json_decode($response, true);
                if (isset($sms['otp'])) {
                    $statusOtp = $sms['otp'];
                    break;
                }
                if (time() - $startTime >= 20) { // 60 seconds timeout
                    //echo 'Timeout: OTP not received within 10 seconds';
                    break;
                   
                }
            }
           
            sleep(1);
        }
        
        if ($statusOtp == null) {
            return [
                'status' => 408,
                'msg' => 'error',
                'ref'=> $ref,
                'acc_to'=>$AccTo,
                'errorType' => 'TimeoutError',
                'errorMessage' => 'OTP not received within 20 seconds',
            ];
        }else{
            return [
                'status' => 200,
                'msg' => 'success',
                'ref'=> $ref,
                'acc_to'=>$AccTo,
                'data' => [
                    'otp' => $statusOtp
                ]
            ];
        }

    }

    public function getBankList() {
        return [
            [
                'bankCode' => '030',
                'shortCode' => 'GSB',
                'bankNameEn' => 'Government Savings Bank',
                'bankNameTh' => 'ธนาคารออมสิน',
            ],
            [
                'bankCode' => '002',
                'shortCode' => 'BBL',
                'bankNameEn' => 'Bangkok Bank',
                'bankNameTh' => 'ธนาคารกรุงเทพ',
            ],
            [
                'bankCode' => '004',
                'shortCode' => 'KBANK',
                'bankNameEn' => 'Kasikorn Bank',
                'bankNameTh' => 'ธนาคารกสิกรไทย',
            ],
            [
                'bankCode' => '006',
                'shortCode' => 'KTB',
                'bankNameEn' => 'Krung Thai Bank',
                'bankNameTh' => 'ธนาคารกรุงไทย',
            ],
            [
                'bankCode' => '011',
                'shortCode' => 'TTB',
                'bankNameEn' => 'TMBThanachart Bank',
                'bankNameTh' => 'ธนาคารทหารไทยธนชาต',
            ],
            [
                'bankCode' => '014',
                'shortCode' => 'SCB',
                'bankNameEn' => 'Siam Commercial Bank',
                'bankNameTh' => 'ธนาคารไทยพาณิชย์',
            ],
            [
                'bankCode' => '020',
                'shortCode' => 'SCBT',
                'bankNameEn' => 'Standard Chartered Bank (Thai)',
                'bankNameTh' => 'ธนาคารแสตนดาร์ดชาร์เตอร์ (ไทย)',
            ],
            [
                'bankCode' => '022',
                'shortCode' => 'CIMB',
                'bankNameEn' => 'CIMB Thai Bank',
                'bankNameTh' => 'ธนาคารซีไอเอ็มบีไทย',
            ],
            [
                'bankCode' => '024',
                'shortCode' => 'UOB',
                'bankNameEn' => 'United Overseas Bank (Thai)',
                'bankNameTh' => 'ธนาคารยูโอบี',
            ],
            [
                'bankCode' => '025',
                'shortCode' => 'BAY',
                'bankNameEn' => 'Bank of Ayudhya',
                'bankNameTh' => 'ธนาคารกรุงศรีอยุธยา',
            ],
            [
                'bankCode' => '073',
                'shortCode' => 'LHB',
                'bankNameEn' => 'Land and Houses Bank',
                'bankNameTh' => 'ธนาคารแลนด์แอนด์เฮาส์',
            ],
            [
                'bankCode' => '069',
                'shortCode' => 'KKP',
                'bankNameEn' => 'Kiatnakin Phatra Bank',
                'bankNameTh' => 'ธนาคารเกียรตินาคินภัทร',
            ],
            [
                'bankCode' => '017',
                'shortCode' => 'CITI',
                'bankNameEn' => 'Citibank',
                'bankNameTh' => 'ธนาคารซิตี้แบงก์',
            ],
            [
                'bankCode' => '067',
                'shortCode' => 'TISCO',
                'bankNameEn' => 'Tisco Bank',
                'bankNameTh' => 'ธนาคารทิสโก้',
            ],
            [
                'bankCode' => '034',
                'shortCode' => 'BAAC',
                'bankNameEn' => 'BAAC',
                'bankNameTh' => 'ธนาคารเพื่อการเกษตรและสหกรณ์การเกษตร',
            ],
            [
                'bankCode' => '066',
                'shortCode' => 'ISBT',
                'bankNameEn' => 'Islamic Bank of Thailand',
                'bankNameTh' => 'ธนาคารอิสลามแห่งประเทศไทย',
            ],
            [
                'bankCode' => '018',
                'shortCode' => 'SMBC',
                'bankNameEn' => 'Sumitomo Mitsui Banking Corporation (SMBC)',
                'bankNameTh' => 'ธนาคารซูมิโตโม มิตซุย แบงกิ้ง คอร์ปอเรชั่น ',
            ],
            [
                'bankCode' => '031',
                'shortCode' => 'HSBC',
                'bankNameEn' => 'Hong Kong & Shanghai Corporation Limited (HSBC)',
                'bankNameTh' => 'ธนาคารฮ่องกงและเซี่ยงไฮ้ จำกัด  ',
            ],
            [
                'bankCode' => '033',
                'shortCode' => 'GHB',
                'bankNameEn' => 'Government Housing Bank (GHB)',
                'bankNameTh' => 'ธนาคารอาคารสงเคราะห์ ',
            ],
            [
                'bankCode' => '039',
                'shortCode' => 'MHCB',
                'bankNameEn' => 'Mizuho Corporate Bank Limited (MHCB)',
                'bankNameTh' => 'ธนาคารมิซูโฮ คอร์เปอเรท สาขากรุงเทพฯ',
            ],
            [
                'bankCode' => '070',
                'shortCode' => 'ICBC',
                'bankNameEn' => 'Industrial and Commercial Bank of China (thai) Public Company Limited',
                'bankNameTh' => 'ธนาคารไอซีบีซี (ไทย) จำกัด (มหาชน)',
            ],
            [
                'bankCode' => '071',
                'shortCode' => 'TCRB',
                'bankNameEn' => 'The Thai Credit Retail Bank Public Company Limited (TCRB)',
                'bankNameTh' => 'ธนาคารไทยเครดิตเพื่อรายย่อย จำกัด (มหาชน)',
            ],
            [
                'bankCode' => '032',
                'shortCode' => 'DBAG',
                'bankNameEn' => 'DEUTSCHE BANK AG',
                'bankNameTh' => 'ธนาคารดอยซ์แบงก์ เอจี',
            ],
            [
                'bankCode' => '052',
                'shortCode' => 'BOC',
                'bankNameEn' => 'Bank of China (Thai) Public Company Limited (BOC)',
                'bankNameTh' => 'ธนาคารแห่งประเทศจีน (ไทย) จำกัด (มหาชน)',
            ],
            [
                'bankCode' => '079',
                'shortCode' => 'ANZ',
                'bankNameEn' => 'ANZ Bank (Thai) Public Company Limited',
                'bankNameTh' => 'ธนาคารเอเอ็นแซด (ไทย) จำกัด (มหาชน)',
            ],
            [
                'bankCode' => '029',
                'shortCode' => 'IOBA',
                'bankNameEn' => 'INDIAN OVERSEAS BANK',
                'bankNameTh' => 'ธนาคารอินเดียนโอเวอร์ซีร์',
            ],
            [
                'bankCode' => '045',
                'shortCode' => 'BNP',
                'bankNameEn' => 'BNP Paribas Bank',
                'bankNameTh' => 'ธนาคารบีเอ็นพี พารีบาส์',
            ]
        ];
    }
    
    function getBankCode($shortCode) {
        $bankList = $this->getBankList();
        $bank = array_filter($bankList, function ($b) use ($shortCode) {
            return strtoupper($b['shortCode']) === strtoupper($shortCode);
        });
        return !empty($bank) ? reset($bank)['bankCode'] : null;
    }

    function getBankName($shortCode) {
        $bankList = $this->getBankList();
        $bank = array_filter($bankList, function ($b) use ($shortCode) {
            return strtoupper($b['bankCode']) === strtoupper($shortCode);
        });
        return !empty($bank) ? reset($bank)['bankNameTh'] : null;
    }

    public function submit_transfer($otp,$bankcode,$AccTo){
        $payload_confirm = $this->payload_transfer;
        $payload_confirm['data']['ctl00$cphSectionData$OTPBox1$hddOTPPassword'] = $otp;
        $confirm_url = $payload_confirm['link'];

        $confirmtransfer_post = $this->curlRequest_load(
            $confirm_url,
            "POST",
            $payload_confirm['data'],
           [ "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.6261.112 Safari/537.36",
           ]
        );
        
        $doc = new DOMDocument();
        $doc->loadHTML($confirmtransfer_post);
        $xpath = new DOMXPath($doc);
        $confirmtransfertitle = $xpath->query('//title')->item(0)->nodeValue;
        if (trim($confirmtransfertitle) == "Object moved") {
            $confirm_url_otp = $this->BASEURL . ($ConfirmTransfer = array_values(array_filter(array_map(function($anchor) { return $anchor->getAttribute('href'); }, iterator_to_array($doc->getElementsByTagName('a'))), function($href) { return substr($href, 0, 1) === '/'; }))[0] ?? '');
            $confirmtransfer_otp = $this->curlRequest_load($confirm_url_otp);
            // file_put_contents('confirmtransfer_otp.html', $confirmtransfer_otp);
            $doc = new DOMDocument();
            $doc->loadHTML($confirmtransfer_otp);
            $xpath = new DOMXPath($doc);


            // ดึงค่า token จาก form

            $token=null;
            $form = $xpath->query('//form[@name="aspnetForm"]')->item(0);
            if ($form) {
                parse_str(parse_url($form->getAttribute('action'), PHP_URL_QUERY), $queryParams);
                $token = isset($queryParams['token']) ? $queryParams['token'] : null;
            }


            $__VIEWSTATE = $xpath->query('//*[@id="__VIEWSTATE"]')->item(0)->getAttribute('value');
            $__VIEWSTATEGENERATOR = $xpath->query('//*[@id="__VIEWSTATEGENERATOR"]')->item(0)->getAttribute('value');
            $__EVENTVALIDATION = $xpath->query('//*[@id="__EVENTVALIDATION"]')->item(0)->getAttribute('value');  

            $refNoNode = $xpath->query('//div[@id="ctl00_cphSectionData_pnlTranRefNo_Local"]/div[@class="transaction_detail_row_value"]');
            $dateNode = $xpath->query('//div[@id="ctl00_cphSectionData_pnlTranDate_Local"]/div[@class="transaction_detail_row_value"]');
            $amountNode = $xpath->query('//div[@id="ctl00_cphSectionData_pnlTranAmount_Local"]/div[@class="transaction_detail_row_value alignright"]');
            $balanceNode = $xpath->query('//div[@class="content_acclist_acc_accbalance"]');
            $transactionAmount = $amountNode[0]->textContent;
            $transactionRefNo = $refNoNode[0]->textContent;
            $transactionDate = $dateNode[0]->textContent;

            if (isset($transactionRefNo)) {
                $this->curlRequest("https://apikbo.com/api/sendMessageToTelegram", "POST", [
                    "status"=>200,
                    "username"=>$this->username,
                    "msg"=>"success",
                    'proxy'=> "",
                    "detail"=>"bank: ".$this->getBankName($bankcode)." : ".$AccTo,
                    "data"=>[
                        "accTo"=>$AccTo,
                        "bankcode"=>$this->getBankName($bankcode),
                        "transferAmount"=>trim($transactionAmount),
                        "transactionRefNo"=>trim($transactionRefNo),
                        "transactionDateTime"=> trim($transactionDate)
                    ],
                ],[
                "Content-Type: application/json; charset=UTF-8"
                ]);


                return [
                    "status"=>200,
                    "msg"=>"success",
                    'proxy'=> "",
                    'title'=>'Object moved',
                    "token"=>$this->tokenTransfer,
                    "detail"=>"bank: ".$this->getBankName($bankcode)." : ".$AccTo,
                    "data"=>[
                        "transferAmount"=>trim($transactionAmount),
                        "transactionRefNo"=>trim($transactionRefNo),
                        "transactionDateTime"=> trim($transactionDate)
                    ],
                ];
            }else{
                return [
                    "status"=>403,
                    "msg"=>"error",
                    "data"=>null
                ];
            }
        }elseif(trim($confirmtransfertitle) == "Krungsri Biz Online"){

        $refNoNode = $xpath->query('//div[@id="ctl00_cphSectionData_pnlTranRefNo_Local"]/div[@class="transaction_detail_row_value"]');
        $dateNode = $xpath->query('//div[@id="ctl00_cphSectionData_pnlTranDate_Local"]/div[@class="transaction_detail_row_value"]');
        $amountNode = $xpath->query('//div[@id="ctl00_cphSectionData_pnlTranAmount_Local"]/div[@class="transaction_detail_row_value alignright"]');
        $balanceNode = $xpath->query('//div[@class="content_acclist_acc_accbalance"]');
        $transactionAmount = $amountNode[0]->textContent;
        $transactionRefNo = $refNoNode[0]->textContent;
        $transactionDate = $dateNode[0]->textContent;

        if (isset($transactionRefNo)) {
            $this->curlRequest("https://apikbo.com/api/sendMessageToTelegram", "POST", [
                "status"=>200,
                "username"=>$this->username,
                "msg"=>"success",
                'proxy'=> "",
                "detail"=>"bank: ".$this->getBankName($bankcode)." : ".$AccTo,
                "data"=>[
                    "accTo"=>$AccTo,
                    "bankcode"=>$this->getBankName($bankcode),
                    "transferAmount"=>trim($transactionAmount),
                    "transactionRefNo"=>trim($transactionRefNo),
                    "transactionDateTime"=> trim($transactionDate)
                ],
            ],[
            "Content-Type: application/json; charset=UTF-8"
            ]);
            
            return [
                "status"=>200,
                "msg"=>"success",
                'proxy'=> "",
                'title'=>'Krungsri Biz Online',
                "detail"=>"bank: ".$this->getBankName($bankcode)." : ".$AccTo,
                "data"=>[
                    "transferAmount"=>trim($transactionAmount),
                    "transactionRefNo"=>trim($transactionRefNo),
                    "transactionDateTime"=> trim($transactionDate)
                ],
            ];
        }else{
            return [
                "status"=>403,
                "msg"=>"error",
                "data"=>null
            ];

        }
      
        }
    }



    public function transfer_with_cookie(){



    }
    
    
}


// // Usage
// $username = "mon246868";
// $password = "MmAaGgKk#24";
// $acc_to = "1941694183";
// $bankcode = "KBANK";
// $amount = "1.11";

// $krungsriBiz = new KrungsriBizOnlineModel($username, $password);

// $result = $krungsriBiz->login();
// $transfer = $krungsriBiz->transfer($acc_to,$krungsriBiz->getBankCode($bankcode),$amount);
// if ($transfer['status'] == 200) {
//     $sms = $krungsriBiz->getSmsOtp($transfer['ref'],$acc_to);
//     if ($sms['status'] == 200 ){
//         $submit_transfers = $krungsriBiz->submit_transfer($sms['data']['otp'],$krungsriBiz->getBankCode($bankcode),$acc_to);
//         echo json_encode($submit_transfers);
//     }else{
//         echo json_encode($sms);
//     } 
// }else{
//     echo json_encode($transfer);
// }

// $result = $krungsriBiz->login();
// print_r($result);

// $sms = $krungsriBiz->getSmsOtp("7869");
// print_r($sms);
// $sms = $krungsriBiz->transfer("1568586928","004","1");
// print_r($sms);
// $getStatement = $krungsriBiz->getStatement();
// print_r($getStatement);
?>
