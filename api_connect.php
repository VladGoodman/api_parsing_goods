<?php


namespace App;

class Connect
{
    public $url;
    private $method;
    public $result_url;
    public $token;
    public $start;
    public $end;
    public $sleep = 5;

    public function __construct($url, $method, $start, $end)
    {
        $this->url = $url;
        $this->method = $method;
        $this->start = $start;
        $this->end = $end;
        $this->token = file_get_contents('token.txt');
        $this->result_url = "https://mpstats.io/api/wb/" . $this->url;
    }

    public function getInfoForApi()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->result_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_POSTFIELDS => "{\"startRow\":$this->start,\"endRow\":$this->end,\"filterModel\":{},\"sortModel\":[{\"colId\":\"id\",\"sort\":\"asc\"}]}",
            CURLOPT_HTTPHEADER => array(
                "X-Mpstats-TOKEN: " . $this->token,
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $getInfo = curl_getinfo($curl);
        curl_close($curl);

        if($getInfo['http_code'] !== 200 ){
            if($getInfo['http_code'] === 202){
                print_r("\n[".date('H:i:s')."] Апи долго не отвечает, перезапуск через $this->sleep секунд...\n");
                sleep($this->sleep);
                $this->getInfoForApi();
            }
            if($getInfo['http_code'] === 401){
                exit("\n[".date('H:i:s')."] Токен в файле token.txt не является действительным\n");
            }
            if($getInfo['http_code'] === 429){
                exit("\n[".date('H:i:s')."] Допустимое колличество запросов закончилось\n");
            } else{
                print_r("\n[".date('H:i:s')."] Апи долго не отвечает, перезапуск через $this->sleep секунд...\n");
                sleep($this->sleep);
                $this->getInfoForApi();
            }
        }else{
            return $response;
        }
    }
}
