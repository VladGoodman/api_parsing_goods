<?php

include './api_connect.php';

use App\Connect;

$filename = 'TrueCategory.csv';

class Parser
{
    private $mass = [];
    private $count_category = 0;


    public function loggingForCategory($filename)
    {
        file_put_contents('log_ready.csv', '');
        file_put_contents('log_ready.csv', "Категория;Неделя;Продаж;Выручка;Товаров;Товаров с продажами;Брендов;Брендов с продажами;Продавцов;Продавцов с продажами;Выручка на товар" . PHP_EOL);
        if (file_exists($filename) and is_readable($filename)) {
            $info_categories = file_get_contents('log_category.csv');
            $rows_categories = explode("\n", $info_categories);
            if (!empty($rows_categories)) {
                $count_string = 0;
                echo "Категорий найдено: $this->count_category".PHP_EOL;
                foreach ($rows_categories as $data) {
                    $request = new Connect('get/category/trends', '2021-07-05', $data, "GET");
                    $result = json_decode($request->getInfoForApi());
                    if ($result) {
                        foreach ($result as $info_category) {
                            file_put_contents('log_ready.csv', str_replace(["\r\n", "\r", "\n"], "", urldecode($data) . ";" . $info_category->week . ";" . $info_category->sales . ";" . $info_category->revenue . ";" . $info_category->items . ";" . $info_category->items_with_sells . ";" . $info_category->brands . ";" . $info_category->brands_with_sells . ";" . $info_category->sellers . ";" . $info_category->sellers_with_sells . ";" . $info_category->product_revenue) . PHP_EOL, FILE_APPEND);
                        }
                    }
                    $count_string++;
                    echo "\rОбработано категорий: $count_string";
                }
            }
        }
    }

    public function parseCategory($filename)
    {
        file_put_contents('log_category.csv', '');
        if (file_exists($filename) and is_readable($filename)) {
            $request = new Connect('get/categories', '', '', "GET");
            $result = json_decode($request->getInfoForApi());
            foreach ($result as $item) {
                if ($item->path !== "") {
                    $this->count_category += 1;
                    file_put_contents($filename, urlencode($item->path)."\n", FILE_APPEND);
                }
            }
        }
    }

}

$parser = new Parser;
$parser->parseCategory('log_category.csv');
$parser->loggingForCategory('log_category.csv');
