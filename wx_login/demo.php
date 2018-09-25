<?php

include_once "wxBizDataCrypt.php";


$appid = 'wx4552387af63efaec';
$sessionKey = 'd2c84f54d4639751fb9663373647b573';

$encryptedData="yRqizmkg/9U4JdrEGJEuDXnPWlZrfEuP1btdtAIzg4hykp6uSYIpfdndLX87MNsKv4HhqqQWo+q4c+64+RkXh13zPgqf1fnvrXumjG9T0/1OKyPCORUfZfCyu5Snewnir8zjYlC9uLikY9YjMkvXgXuESAhnhJLd9AFUZGDq3bjRXDfjR4vRLEmFgFrBVJT6tYYVsnbgB9LTKqfCBxw/pJiGyB0bftS/a67p6QmQlXCV/JLPVmGp3sm8lEs86OjSkMACEFalsLE2Vdt4R5O/oDcqY92aRXUuJKmw/9wpZQfqvp9k3yrnkLEmEeKkZJzfBbUPIw6Hwt5rGq5UpAlvk8j30xtSVGEu3Kkabt9x0WCD7EccRgOvAz9BZ64FY8MLkI3fGRAfNe1bTG7oyp+kKkK7cfmWdatTFytgnVlfy1iDj2i9K4Qpt6M/tpLRpUFZO4g+GNfeuLS+59EhyBhB5g==";

$iv = 'IAsU/IDavtbH3zQou6Fp+w==';

$pc = new WXBizDataCrypt($appid, $sessionKey);
$errCode = $pc->decryptData($encryptedData, $iv, $data );

if ($errCode == 0) {
    print($data . "\n");
} else {
    print($errCode . "\n");
}
