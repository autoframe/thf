<?php

namespace smsapi;
    
// --------------------------------------------------------------------------------------------------
//   
//    SMS Gateway HTTP(S) integration with SMSLink.ro
//     - Version 1.4 / 19-04-2017
//     
// --------------------------------------------------------------------------------------------------

// --------------------------------------------------------------------------------------------------
//  
//      Class Implementation
//      
// --------------------------------------------------------------------------------------------------
class Smslink extends SmsApi
{       
    // ----------------------------------------------------------------------------------------------   
    //   Change the variabiles to match your account details    
    //      - Connection ID and Password can be generated at www.smslink.ro/sms/gateway/setup.php
    //        after authenticated with your Username or E-mail and Password
    // ----------------------------------------------------------------------------------------------   
    public $ConnectionID = "A362257A40117C12";      // SMS Gateway Connection ID
    public $Password = "anuntVerificat@imobiliare.ro";          // SMS Gateway Password
    
    // ----------------------------------------------------------------------------------------------
    //   Configuration Parameters
    // ----------------------------------------------------------------------------------------------
    public $Protocol = "http://";   // Accepted Values: http:// or https://
    
    public $RequestMethod = 2;      // If set to 1, request is made using PHP file_get_contents.
                                    //    PHP file_get_contents require allow_url_fopen to be set
                                    //    to 1 in php.ini (default value)
                                    // If set to 2, request is made using PHP CURL functions
    
    // ----------------------------------------------------------------------------------------------   
    //   Recommendation: Do not change below this line WITHIN the CLASS
    // ----------------------------------------------------------------------------------------------
    public $Address = NULL;
    public $Logs = array();
            
    // -------------------------------------------------------------------------------------------------
    //   Class Initialization
    // -------------------------------------------------------------------------------------------------
    public function __construct()
    {
        $Address = $this->Protocol.
                   (($this->Protocol == "http://") ? "www." : "secure.").
                   "smslink.ro/sms/gateway/communicate/?connection_id=".
                   "[connection_id]&password=[password]";
                                          
        $this->Address = str_replace(
                            array("[connection_id]", "[password]"), 
                            array($this->ConnectionID, $this->Password), 
                            $Address
                           );
                                        
    }
    
    // ------------------------------------------------------------------------------------------------------
    //   public function SendMessage
    //     - Description: Sends SMS by sending a request to SMS Gateway at SMSLink.ro.
    //     - Variabiles:
    //          - (string) $Receiver            - Receiver mobile number, national format: 07XYZZZZZZ
    //          - (string) $Message             - Text SMS, up to 160 alphanumeric characters, or longer
    //                                            than 160 characters. Recommended to be used with GSM7 IA5 
    //                                            alphabet (QWERTY characters).
    //
    //          - (string) $Sender              - (Optional) Sender alphanumeric string for SMS:
    //
    //              numeric    - sending will be done with a shortcode (ex. 18xy, 17xy)
    //              SMSLink.ro - sending will be done with SMSLink.ro (use this for tests only)
    //
    //              Any other preapproved alphanumeric sender assigned to your account:
    //                - Your alphanumeric sender list:        http://www.smslink.ro/sms/sender-list.php
    //                - Your alphanumeric sender application: http://www.smslink.ro/sms/sender-id.php
    //
    //              Please Note:
    //                - SMSLink.ro sender should be used only for testing and is not recommended to be used
    //                  in production. Instead, you should use numeric sender or your alphanumeric sender,
    //                  if you have an alphanumeric sender activated with us.
    //                - If you set an alphanumeric sender for a mobile number that is in a network where the
    //                  alphanumeric sender has not been activated, the system will override that setting
    //                  with numeric sender.
    //
    //          - (int)    $TimestampProgrammed - (Optional) Should be 0 (zero) for immediate sending or  
    //                                            other UNIX timestamp in the future for future sending
    //
    //     - Returns: (int) representing SMSLink Message ID on success or false on failure.
    // ------------------------------------------------------------------------------------------------------
    public function send($Receiver, $Message, $sSmsId = 0, $Sender = 'imobiliare', $TimestampProgrammed = 0)
    {     
        $Message = urlencode($Message);
        
        $Result = $this->SendRequest(
                        $this->Address.
                        "&to=".$Receiver.
                        "&message=".$Message.
                        ((!is_null($Sender)) ? "&sender=".$Sender : "").
                        (($TimestampProgrammed > 0) ? "&timestamp=".$TimestampProgrammed : "")
                     ); 

        $MessageID = false;
        
        if ($Result != false)
        {       
            $Response = array();
                   
            $parts = explode(";", $Result);

            if (isset($parts[0])) {
                $Response['level'] = $parts[0];
            }

            if (isset($parts[1])) {
                $Response['id'] = $parts[1];
            }

            if (isset($parts[2])) {
                $Response['description'] = $parts[2];
            }

            if (isset($parts[3])) {
                $Response['var'] = $parts[3];
            }

            $this->Logs[] = $Response;
                 
            if (($Response["level"] == "MESSAGE") and ($Response["id"] == 1))
            {
                $MessageID = $this->ArrayValue(explode(",", $Response["var"]));

            }
            else
            {
                if ($Response["level"] == "ERROR") {
                    throw new \Exception($this->getErrorMessage($Receiver, $Response['description']));
                }
            }
                
        }
        
        return $MessageID;
            
    }               
    
    // ------------------------------------------------------------------------------------------------------
    //   public function Balance
    //     - Description: Returns account SMS balance at SMSLink.ro.
    //     - Returns: (array) representing an array containing SMSLink account balance and timestamp on 
    //                success or false on failure.
    // ------------------------------------------------------------------------------------------------------
    public function Balance()
    {
        $Result = $this->SendRequest(
                        $this->Address.
                        "&mode=credit"
                     ); 

        $Balance = false;
        
        if ($Result != false)
        {       
            $Response = array();
                        
            list($Response["level"],
                 $Response["id"], 
                 $Response["description"],
                 $Response["var"]) = explode(";", $Result);

            $this->Logs[] = $Response;
                         
            if (($Response["level"] == "MESSAGE") and ($Response["id"] == 2))
            {
                $Balance = explode(",", $Response["var"]);
            }
            else
            {
                if ($Response["level"] == "ERROR") 
                    $Response = false;

            }
                
        }
        
        return $Balance; 
        
    }

    // ------------------------------------------------------------------------------------------------------
    //
    //   Various Internal Functions
    //
    // ------------------------------------------------------------------------------------------------------
    private function SendRequest($URL)
    {
        $Result = false;
                
        if ($this->RequestMethod == 1)
        {
            $Result = file_get_contents($URL);    
        }
        else
        {
            if ($this->RequestMethod == 2)
            {
                $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL, $URL);

                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                
                // -----------------------------------------------------
                //  For increased performance,  we recommend  setting
                //  CURLOPT_SSL_VERIFYPEER and CURLOPT_SSL_VERIFYHOST
                //  to false, as in implementation below. 
                // -----------------------------------------------------
                if (strpos($URL, "https://") !== false)
                {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                }

                $Result = curl_exec($ch);
                
                curl_close($ch);
                
            }
            
        }
        
        return $Result;
        
    }
    
    private function ArrayValue($data, $key = 0)
    {
        return $data[$key];
        
    }
            
}
    