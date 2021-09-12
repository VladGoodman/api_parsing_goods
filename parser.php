<?php

include './api_connect.php';

use App\Connect;


class Parser
{
    private $restartQueue;
    private $title = [
        'SKU',
        'Name',
        'Color',
        'Category',
        'Category position',
        'Brand',
        'Seller',
        'Balance',
        'Comments',
        'Rating',
        'Final Price',
        'Min price',
        'Max price',
        'Average price',
        'Base price',
        'Basic Sale',
        'Basic Sale Price',
        'Promo Sale',
        'SPP',
        'SPP Price',
        'Revenue potential',
        'Lost profit',
        'Lost profit percent',
        'Days in stock',
        'Days with sales',
        'Average if in stock',
        'Sales',
        'Sales per day average',
        'Revenue'
    ];
    private $count_items = 0;
    private $start = 0;
    private $end = 0;
    private $step = 5000;

    public function __construct($restartQueue)
    {
        $this->restartQueue = $restartQueue;
    }

    public function start()
    {
        $this->checkCountItems();
        $this->buildQueueWithoutDailyStatistics();
        $this->checkQueue();
        $this->parsingGoods();
    }

    public function checkQueue()
    {
        if (
            gettype((int)$this->start) !== "integer"
            or
            gettype((int)$this->end) !== "integer"
        ) {
            exit("\nОшибка очередей, отчистите файле queue.log\n");
        } elseif ($this->start > $this->end) {
            exit("\nОчереди были неправильно определены, отчистите файл queue.log\n");
        }
    }

    public function buildQueueWithoutDailyStatistics()
    {
        if ($this->restartQueue) {
            print_r("Обновление очереди и файлов...\n");
            file_put_contents('system_info/queue.log', '');
            print_r("Очередь обнулена...\n");
            $files = glob('result/*');
            foreach ($files as $key => $file) {
                if (is_file($file)) {
                    unlink($file);
                }
                print_r("\rФайлы обнулены [$key]");
            }
            file_put_contents('system_info/queue.log', "0/$this->count_items\n", FILE_APPEND);
            $this->start = 0;
            $this->end = $this->count_items;
            print_r("\nОчередь обновлена : 0/$this->count_items\n");
            return 1;
        }
        if (!file_get_contents('system_info/queue.log')) {
            print_r("Начальное построение очереди...\n");
            file_put_contents('system_info/queue.log', '');
            file_put_contents('system_info/queue.log', "0/$this->count_items\n", FILE_APPEND);
            $this->start = 0;
            $this->end = $this->count_items;
            print_r("Очередь установлена : 0/$this->count_items\n");
            return 1;

        } else {
            print_r("Продолжение очереди...\n");
            $info_queue = file_get_contents('system_info/queue.log');
            $last_queue = explode("\n", $info_queue);
            $count_queue = explode('/', $last_queue[count($last_queue) - 2]);
            $this->count_items = $count_queue[1];
            $this->start = $count_queue[0];
            $this->end = $count_queue[1];
            print_r("Обработано товаров : $this->start/$this->end\n");
            return 1;
        }
    }

    public function addInfoForQueue($start, $end)
    {
        file_put_contents('system_info/queue.log', "$start/$end\n", FILE_APPEND);
        print_r("\nПозиция в очереди : $start/$end");
    }

    public function checkCountItems()
    {
        print_r("\nОпределение колличества товаров...");
        $request = $this->checkForCountAPI();
        $result = json_decode($request->getInfoForApi());
        if (!array_key_exists('total', $result)) {
            exit("\nАпи не отвечает, попробуйте перезапустить скрипт\n");
        }
        $this->count_items = $result->total;
        print_r("\nКолличество товаров : $this->count_items\n");
    }

    private function addTitleForReadyLog($filename)
    {
        print_r('Установка заголовков...' . "\n");
        file_put_contents($filename, '');
        file_put_contents($filename, implode(';', $this->title));
    }

    public function getFileLogName()
    {
        $start_log = floor(($this->start + 1) / 1000000) * 1000000;
        return ("result/" . $start_log . "-" . ($start_log + 1000000) . '.csv');
    }

    public function checkAPI($item)
    {
        if (!$request = new Connect('get/category', 'POST', $item, $item + $this->step)) {
            return false;
        }
        return $request;
    }

    public function checkForCountAPI()
    {
        if (!$request = new Connect('get/category', "POST", 0, 1)) {
            return false;
        }
        return $request;
    }

    public function parsingGoods()
    {
        $filename_logs = $this->getFileLogName();
        print_r("Запуск получения информации о товарах...\n");

        for ($item = $this->start; $item <= $this->end + $this->step; $item += $this->step) {
            if ($item === 0 or $item % 1000000 === 0) {
                $filename_logs = "result/" . $item . "-" . ($item + 1000000) . '.csv';
                if (!file_exists($filename_logs)) {
                    $this->addTitleForReadyLog($filename_logs);
                }
            }
            $this->checkQueue();
            print_r("\nЗапись в файл $filename_logs ...");
            if (!$request = $this->checkAPI($item)) {
                print_r("\nAPI не ответил на запрос, попробуйте перезапустить скрипт\n");
                break;
            }
            $result = json_decode($request->getInfoForApi());
            if (!array_key_exists('total', $result)) {
                exit("\n187: Апи не отвечает, попробуйте запустить скрипт ещё раз\n");
            }
            if ($result) {
                foreach ($result->data as $item_info) {
                    file_put_contents($filename_logs,
                        PHP_EOL .
                        $item_info->id
                        . ";" . $item_info->name
                        . ";" . $item_info->color
                        . ";" . $item_info->category
                        . ";" . $item_info->category_position
                        . ";" . $item_info->brand
                        . ";" . $item_info->seller
                        . ";" . $item_info->balance
                        . ";" . $item_info->comments
                        . ";" . $item_info->rating
                        . ";" . $item_info->final_price
                        . ";" . $item_info->final_price_min
                        . ";" . $item_info->final_price_max
                        . ";" . $item_info->final_price_average
                        . ";" . $item_info->basic_price
                        . ";" . $item_info->basic_sale
                        . ";" . $item_info->start_price
                        . ";" . $item_info->promo_sale
                        . ";" . $item_info->client_sale
                        . ";" . $item_info->client_price
                        . ";" . $item_info->revenue_potential
                        . ";" . $item_info->lost_profit
                        . ";" . $item_info->lost_profit_percent
                        . ";" . $item_info->days_in_stock
                        . ";" . $item_info->days_with_sales
                        . ";" . $item_info->average_if_in_stock
                        . ";" . $item_info->sales
                        . ";" . $item_info->sales_per_day_average
                        . ";" . $item_info->revenue

                        , FILE_APPEND);
                }
            }
            $this->addInfoForQueue($item + $this->step, $this->count_items);
            $result = null;
        }
        print_r("\n--------------------\nОБРАБОТКА ЗАВЕРШЕНА\n--------------------\n");
    }
}

$parser = new Parser(false);
$parser->start();
