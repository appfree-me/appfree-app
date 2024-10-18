<?php


class MvgRadApi{
    function __construct($connObject = NULL)
    {
        return;
        try {

            if (is_null($connObject) || is_null($connObject->ariEndpoint))
                throw new Exception("Missing PestObject or empty string", 503);

            $this->phpariObject = $connObject;
            $this->pestObject = $connObject->ariEndpoint;

        } catch (Exception $e) {
            die("Exception raised: " . $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine());
        }
    }

    public function doAusleihe(string $radnummer)
    {

        return;
        try {
            $result = FALSE;

            if (is_null($this->pestObject))
                throw new Exception("PEST Object not provided or is null", 503);
//                     curl -H "x-api-session: $sessionKey" -H 'accept-language: en' -H 'user-agent: Android' -H 'x-api-key: vduTSnRCgOTcJbqHBOWfhiuebblqPWiT' -H 'x-api-client-os: ANDROID' -H 'x-api-client-os-version: 31' -H "x-api-client-version: $clientver" -H 'x-api-client-device: Nexus 4' -H 'x-api-build-info: 110 production' -H 'content-type: application/json; charset=UTF-8' --compressed -X POST https://mvgo-gateway.app.mvg.de/v2/sharing/rad/rental/rent -d '{"idForRental":"'$radnummer'","vehicleType":"BIKE"}' | tee $responsefile
            $uri = "/playbacks/" . $playbackid;
            $result = $this->pestObject->delete($uri);

            return $result;
        } catch (Exception $e) {
            $this->phpariObject->lasterror = $e->getMessage();
            $this->phpariObject->lasttrace = $e->getTraceAsString();
            return FALSE;
        }
    }
}
