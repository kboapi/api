<?php
// require_once('../auth/Middleware.php');



header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// header('Content-Type: application/json; charset=utf-8');
require_once("KBiz.class.php");
date_default_timezone_set('Asia/Bangkok');
error_reporting(0);
function readJSONFile($filename) {
    $jsonString = file_get_contents($filename);
    return json_decode($jsonString, true);
}

function writeJSONFile($filename, $data) {
    file_put_contents($filename, json_encode($data));
}

function sendMessageToTelegram($botToken, $chatId, $text) {
    $url = "https://api.telegram.org/bot$botToken/sendMessage";

    $postData = array(
        'chat_id' => $chatId,
        'text' => $text
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL Error: $error");
    }

    curl_close($ch);

    return $response;
}


$botToken = '6757999965:AAFI6TjS21_DQH-SqO7HZnzB_x7bTXOzB30';
$chatId = '-1002224284016';



$data = json_decode(file_get_contents('php://input'), true);
$username = isset($data['username']) ? $data['username'] : "";
$password = isset($data['password']) ? $data['password'] : "";
$device = isset($data['device']) ? $data['device'] : "";
$start = isset($data['start_date']) ? $data['start_date'] : null;
$end = isset($data['end_date']) ? $data['end_date'] : null;
$pageNo = isset($data['pageNo']) ? $data['pageNo'] : 1;
$rowPerPage = isset($data['rowPerPage']) ? $data['rowPerPage'] : 10;
$cookie =  './cookies/'.$username . ".txt";




// $current_time = date('H:i'); // เวลาปัจจุบันในรูปแบบ ชั่วโมง:นาที (24 ชั่วโมง)
// $start_time = '02:00'; // เวลาเริ่มต้นของช่วงที่ต้องการตรวจสอบ
// $end_time = '11:00'; // เวลาสิ้นสุดของช่วงที่ต้องการตรวจสอบ
// // ดึงชั่วโมงและนาทีจากเวลาปัจจุบัน
// $current_time_hour = intval(date('H'));
// $current_time_min = intval(date('i'));

// list($start_hour, $start_min) = explode(':', $start_time);
// list($end_hour, $end_min) = explode(':', $end_time);

// // เปรียบเทียบเวลาปัจจุบันกับช่วงที่กำหนด (ช่วงเวลาที่ 1)
// if (($current_time_hour > $start_hour || ($current_time_hour == $start_hour && $current_time_min >= $start_min)) &&
//     ($current_time_hour < $end_hour || ($current_time_hour == $end_hour && $current_time_min <= $end_min))) {

// }else{
//     exit;
// }


// $data_bot = readJSONFile("kbiz_data.json");
// if (!$data_bot[$username] || $data_bot[$username] == false) {
//    exit;
// }
// exit;

$kbank = new KasikornBank($username, $password, "0", "th", $cookie);
$action = isset($data['action']) ? $data['action'] : "getTransaction";
$login = false;

// || $username=="tho088789"
// if ($action == "transfer" || $action == "getBankAccount" || $username=="kbizacc002") {
//     $login = $kbank->Login();
// }
$date = date('Y-m-d H:i:s');
// Telegram bot token and chat ID
$botToken = '6757999965:AAFI6TjS21_DQH-SqO7HZnzB_x7bTXOzB30';
$chatId = '-4586689119';

// $wait_kbiz = file_get_contents($username."_wait.json");
// if ($wait_kbiz == "1") {
//     echo json_encode([
//         "status"=>false,
//         "msg"=>$username." รอโอนบัญชีก่อนหน้า ให้เสร็จก่อน"
//     ]);
//     exit;
// }
// if ($username == "Pimolon17" && $_GET['key'] !== "on" || $username == "Suphachai56" && $_GET['key'] !== "on" ) {
//     # code...
//     exit;
// } 


$login = $kbank->Login();

if ($login) {
    $Account = $kbank->getBankAccount();
    $acc = $Account["ownAccountList"]["0"];
    $refreshSession = $kbank->Session_refresh();
    if ($action == "getTransaction") {

        // exit;
        // $Statement = $kbank->getTransactionList($acc["accountNo"], $acc["accountType"],$start,$end,$pageNo,$rowPerPage);
        // if (isset($Statement)) {
        // // $Statement['captcha'] = false;
        // echo json_encode(array("Statement" => $Statement));
        // // exit;
        // }else{
        // // $kbank->recaptchaVerify();
        // $Statement = $kbank->getTransactionList($acc["accountNo"], $acc["accountType"],$start,$end,$pageNo,$rowPerPage);
        // // $Statement['captcha'] = true;
        // echo json_encode(array("Statement" => $Statement));
        // // exit;
        // }

        $Statement = $kbank->getTransactionList($acc["accountNo"], $acc["accountType"],$start,$end,$pageNo,$rowPerPage);
        echo json_encode(array("Statement" => $Statement));
        exit;

    // $detail = [];
    // $kbank->recaptchaVerify();
    // foreach ($Statement['recentTransactionList'] as &$transaction) {
    //     //$kbank->recaptchaVerify();
    //     $a = $transaction;
    //     $details = $kbank->getTransactionDetail($acc["accountNo"], $transaction);
    //     if ($a['toAccountNumber'] == null) {
    //         $a['toAccountNumber'] =  $details['toAccountNo'];
    //         $a['channelTh'] = $details['bankNameTh'];
    //         $a['channelEn'] = $details['bankNameEn'];
    //         $a['fromAccountNameEn'] = $details['toAccountNameEn'];
    //         $a['fromAccountNameTh'] = $details['toAccountNameTh'];
    //     }
    //     $transaction = $a;
    // }
    
    }elseif ($action == "getBankAccount"){
 
        $Account = $kbank->getBankAccount();
        echo json_encode($Account);
        exit;
    }elseif($action == "getTransactionDetail"){

        $transaction = [
            "debitCreditIndicator" => $data["debitCreditIndicator"],
            "origRqUid" => $data["origRqUid"],
            "originalSourceId" => $data["originalSourceId"],
            "transCode" => $data["transCode"],
            "transDate"=>$data["transDate"],
            "transType"=>$data["transType"]
        ];

        $details = $kbank->getTransactionDetail($acc["accountNo"], $transaction);
        echo json_encode($details);
        exit;

        
        // if (isset($details)) {
        //     // $details['iscaptcha']= false;
            // echo json_encode($details);
            // exit;
        // }else{
        //     // $cap = $kbank->recaptchaVerify();
        //     // $recaptchaVerify = json_decode($cap,true);
        //     $transaction = [
        //         "debitCreditIndicator" => $data["debitCreditIndicator"],
        //         "origRqUid" => $data["origRqUid"],
        //         "originalSourceId" => $data["originalSourceId"],
        //         "transCode" => $data["transCode"],
        //         "transDate"=>$data["transDate"],
        //         "transType"=>$data["transType"]
        //     ];
           
        //     $details = $kbank->getTransactionDetail($acc["accountNo"], $transaction);
        //     // $details['captcha']= $recaptchaVerify;
        //     // $details['iscaptcha']= true;
        //     echo json_encode($details);
        // }
                 
    }elseif($action == "transfer"){

        // if ($username == "boontry" && $_GET['key'] !== "on") {
        //     # code...
        //     exit;
        // } 

        //if ($username == "Sor095502" || $username == "kbizacc003" || $username == "kbizacc002" || $username == "kbizacc008" || $username == "poonsin250436"|| $username == "Nuljun8888" || $username == "suphaporn2105" || $username == "tho088789"  || $username =="Rueangrotn03" ) {
          
        //
        //}


        echo json_encode($kbank->recaptchaVerify());
        exit;

        
        file_put_contents($username."_wait.json","1");
        $bankCode = isset($data['bankcode']) ? $data['bankcode'] : "";
        $amount = isset($data['amount']) ? $data['amount'] : "";
        $acc_to = isset($data['acc_to']) ? $data['acc_to'] : "";

        $bankTransferOrft = $kbank->bankTransferOrft($bankCode, $amount, $acc_to, $acc['accountNoNickNameTH'], $acc['accountNo']);
        // echo json_encode($bankTransferOrft);
        // exit;

      
        if ($bankTransferOrft['errorCode'] == '00' ) {
            $token = $bankTransferOrft['tokenId'];
            
            // if ($username == "kbizacc003") {
            //     // $device = "localhost:5555";
            //     $device = "emulator-5554";
            //     $url = "https://kbiz03.ngrok.app/?device=".$device."&token=".$token;
                // // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                // $adb_kplus = $kbank->request('GET',$url);
                // $adb_kplus['body']['link'] =  $url;
                // $data = $adb_kplus['body'];
            //     file_put_contents($username."_wait.json","0");
            //     echo json_encode($adb_kplus['body']);
            //     exit;
            // }



            if ($username == "suphaporn2105") {
                $device = "localhost:5555";
                // $device = "emulator-5554";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbiz002.ngrok.dev/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET',$url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;
                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;

                
                sendMessageToTelegram($botToken, $chatId, $text);
                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }


            if ($username == "tho088789") {
                // $device = "localhost:5555";
                // $device = "emulator-5554";
                // // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                // $adb_kplus = $kbank->request('GET',"https://kbiz04.ngrok.dev/?device=".$device."&token=".$token);
                // $data = $adb_kplus['body'];


                // $device = "OVN7HM6TMJLFL7UG";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://920d26ee0355fd96.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);

                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;
                
                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                        "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                        "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                        "\nเวลา: " . $date ;


                
                sendMessageToTelegram($botToken, $chatId, $text);

                
             
                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }




            if ($username == "Ty1124") {
         
                $url = "https://a9d86c7ce88f09d8.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);

                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;
                
                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                        "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                        "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                        "\nเวลา: " . $date ;


                
                sendMessageToTelegram($botToken, $chatId, $text);

                
             
                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }


            if ($username == "Jojkpss") {
         
                $url = "https://31573ddad455491f.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);

                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;
                
                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                        "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                        "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                        "\nเวลา: " . $date ;


                
                sendMessageToTelegram($botToken, $chatId, $text);

                
             
                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }




            // if ($username == "Nuljun8888") {
            //     // $device = "emulator-5554";
            //     $device = "localhost:5555";
            //     $url  = "https://kbiz001.ngrok.dev/?device=".$device."&token=".$token;
            //     // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
            //     $adb_kplus = $kbank->request('GET',$url);
            //     $adb_kplus['body']['link'] =  $url;
            //     $data = $adb_kplus['body'];
            //     file_put_contents($username."_wait.json","0");
            //     echo json_encode($adb_kplus['body']);
            //     exit;
            // }


            if ($username == "Suphachai56") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                $url  = "https://kbizsup.ngrok.app/?device=".$device."&token=".$token;
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $adb_kplus = $kbank->request('GET',$url);
                $adb_kplus['body']['link'] =  $url;
                $data = $adb_kplus['body'];

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;



            }


            if ($username == "0994183684m") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                $url  = "https://ef260b190fa94314.ngrok.app/?device=".$device."&token=".$token;
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $adb_kplus = $kbank->request('GET',$url);
                $adb_kplus['body']['link'] =  $url;
                $data = $adb_kplus['body'];

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;

            }


            if ($username == "0838874568Mm") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                $url  = "https://d3af8ca81f5f07b2.ngrok.app/?device=".$device."&token=".$token;
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $adb_kplus = $kbank->request('GET',$url);
                $adb_kplus['body']['link'] =  $url;
                $data = $adb_kplus['body'];

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;

            }

            if ($username == "Rueangrotn03") {
                $device = "localhost:5555";
                // $device = "LNG66HKV4XIZ8DS4";
                $url = "https://kbiz.ngrok.app/?device=".$device."&token=".$token;
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $adb_kplus = $kbank->request('GET',$url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            if ($username == "kbizacc0100") {
                $device = "localhost:5555";
                // $device = "QO8TKZNJX8UKW4LN";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbiz0100.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET',$url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            
            if ($username == "nmtuemetun28") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbiznmtuemetun28.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            if ($username == "rty71805") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://12f7eae845bc8993.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;




                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, '-1002490848803', $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            

            if ($username == "phatcharinsuai02") {
                $device = "localhost:5555";
                // $device = "F65L79CAUKM7KVUC";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbizphat.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            if ($username == "Pimolon17") {
                $device = "localhost:5555";
                // $device = "F65L79CAUKM7KVUC";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbizpim.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }


            if ($username == "pelaiwan") {
                $device = "localhost:5555";
                // $device = "F65L79CAUKM7KVUC";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://5fd97822af32c859.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;

        
                sendMessageToTelegram($botToken, '-1002490848803', $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            if ($username == "boontry") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbizboontry.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }


            if ($username == "Bunsupp") {
               


                echo json_encode($bankTransferOrft);


                exit;
            }





            if ($username == "Rttagh") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://d26ef544feb513f3.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }




            
            if ($username == "Attasd") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://878b668fc3b8b034.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }



            if ($username == "sukanhome02") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbizsukanhome02.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            if ($username == "awayweaa") {
                // $device = "emulator-5554";
                $device = "localhost:5555";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbizawayweaa.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;


                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            if ($username == "Kbizacc0011") {
                $device = "localhost:5555";
                // $device = "YHNF7TY5FIOB79LV";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbiz0011.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;

                $adb_kplus['body']['link'] =  $url;

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }




            if ($username == "far977920") {
                $device = "localhost:5555";
                // $device = "NREIK75D7T6LKBNR";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbizfan.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET', $url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                // sendMessageToTelegram($botToken, '-4575161285', $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            
            if ($username == "monstido222") {
                
                $device = "localhost:5555";
                // $device = "PNNRJVHAPVJBEABI";
                $url = "https://devkbiz.ngrok.app/?device=".$device."&token=".$token;
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $adb_kplus = $kbank->request('GET',$url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            // if ($username == "monstido222") {
                
            //     $device = "localhost:5555";
            //     // $device = "PNNRJVHAPVJBEABI";
            //     $url = "https://devkbiz.ngrok.app/?device=".$device."&token=".$token;
            //     // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
            //     $adb_kplus = $kbank->request('GET',$url);
            //     $data = $adb_kplus['body'];
            //     $adb_kplus['body']['link'] =  $url;

            //     $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
            //     "\nจำนวนเงิน: " . $data['msg']['amount'] . 
            //     "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
            //     "\nเวลา: " . $date ;


        
            //     sendMessageToTelegram($botToken, $chatId, $text);

            //     file_put_contents($username."_wait.json","0");
            //     echo json_encode($adb_kplus['body']);
            //     exit;
            // }





            if ($username == "kbizacc008") {
                $device = "localhost:5555";
                // $device = "R845YHEEPVH6Y5FQ";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                // $adb_kplus = $kbank->request('GET',"https://kbiz008.ngrok.dev/?device=".$device."&token=".$token);
                $url = "https://kbiz008.ngrok.dev/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET',$url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }


            if ($username == "Attaya4827") {
                $device = "localhost:5555";
                // $device = "R845YHEEPVH6Y5FQ";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                // $adb_kplus = $kbank->request('GET',"https://kbiz008.ngrok.dev/?device=".$device."&token=".$token);
                $url = "https://kbizx.ngrok.app/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET',$url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            if ($username == "jarusa123") {
                $device = "localhost:5555";
                // $device = "R845YHEEPVH6Y5FQ";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                // $adb_kplus = $kbank->request('GET',"https://kbiz008.ngrok.dev/?device=".$device."&token=".$token);
                $url = "https://kbizjarusa123.ngrok.app/?device=".$device."&token=".$token;
                
                // echo json_encode($bankTransferOrft);
                $adb_kplus = $kbank->request('GET',$url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                sendMessageToTelegram($botToken, $chatId, $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }



            if ($username == "kbizacc009") {
                # code...
                $device = "localhost:5555";
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $url = "https://kbiz009.ngrok.dev/?device=".$device."&token=".$token;
                $adb_kplus = $kbank->request('GET',$url);
                $data = $adb_kplus['body'];
                $adb_kplus['body']['link'] =  $url;

                $text = "($username)\nโอนเงินสำเร็จ:\n" . $data['msg']['to'] . " " .
                "\nจำนวนเงิน: " . $data['msg']['amount'] . 
                "\nหมายเลขอ้างอิง: " . $data['msg']['number'] . 
                "\nเวลา: " . $date ;


        
                // sendMessageToTelegram($botToken, '-4575161285', $text);

                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }

            if ($username == "poonsin250436") {
                # code...
                $device = "localhost:5555";
                $url = "https://kbiz006.ngrok.dev/?device=".$device."&token=".$token;
                // $adb_kplus = $kbank->request('GET',"https://kbiz.ngrok.app/?device=".$device."&token=".$token);
                $adb_kplus = $kbank->request('GET',"https://kbiz006.ngrok.dev/?device=".$device."&token=".$token);
                $data = $adb_kplus['body'];
                file_put_contents($username."_wait.json","0");
                echo json_encode($adb_kplus['body']);
                exit;
            }
            
        }else{
            $text = "($username)\nโอนไม่เงินสำเร็จ:\n" . json_encode($bankTransferOrft['body']) . " " .
            "\nเวลา: " . $date ;


            
            sendMessageToTelegram($botToken, $chatId, $text);

            echo json_encode($bankTransferOrft);
        }

        //else{
        //     // echo json_encode($bankTransferOrft);
        //     // if(isset($bankTransferOrft['errorDTO'])){
        //     //     exit;
        //     // }  
        // }
        // if ($i >= 3) {
        //     echo json_encode($bankTransferOrft['body']);
        //     exit;
        // }
        // file_put_contents('./qrcode_kbiz/'.$username.'qrcode.json',json_encode($bankTransferOrft));
       

    }
   
}

// }
