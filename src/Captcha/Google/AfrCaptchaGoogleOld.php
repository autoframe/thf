<?php


namespace Autoframe\Core\Captcha\Google;


class AfrCaptchaGoogleOld extends AfrCaptchaGoogle
{


    function getHtmlCaptcha(): string
    {

        return 'alfa 1.1';
    }


    function postCheck(){
        if(count($_POST)) {  //and strlen($_POST['name'] > 5) and strlen($_POST['mesaj_text'])> 5


            $data = array(
                'secret' => RECAPTCHA_SECRET_KEY, //cheia este definita in thf_main.php
                'response' => isset($_POST["g-recaptcha-response"])?$_POST["g-recaptcha-response"]: '',
            );
            $options = array(
                'http' => array (
                    'method' => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($options);
            $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            $captcha_success=json_decode($verify);





            $path='';

            $_POST['message']=str_replace(array(' ','	','-','+',','),array('','','','00',''),$_POST['message']);
            //prea($_POST);
            if ($captcha_success->success==true){
                //TODO
            }
        }
    }

    function recaptchaHtml()
    { ?>
        <script src='https://www.google.com/recaptcha/api.js?hl=<?php echo substr(SELECTED_HTML_LANG, 0, 2); ?>'></script>
        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
        <script>

            $(function () {
                setInterval(function(){
                    if($('input[name=name]').val().length  < 2 || $('input[name=email]').val().length  < 2 || $('textarea[name=mesaj_text]').val().length  < 10 || $('[name="g-recaptcha-response"]').val().length<10){
                        $('#trimite').attr('disabled',true);
                        $('#status').html('');
                    }
                    else { $('#trimite').attr('disabled',false);}
                },500);
            });

        </script>
        <?php
    }


}