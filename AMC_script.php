<?php

require_once('woo_functions.php');
//constant
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR); // used for cross platform for linux and window
}
define('DEBUG', true);
$panel_path = dirname(__FILE__);

/* * *************
 * CsvPaser is processor of the csv for particular client
 *
 *
 * ************* */

class CsvParser {

    public $dirctory_schema;
    public $csv_file_schema;
    private $currentCsvProcess; // current processing csv for internal use
    private $temp_client;
    public $all_csv_file_schema;
    public $current_ini;

    // main function processCsv ::

    public function processCsv($path = '') {
        // scan dir                       
        if ($path == '') {
            $path = dirname(__FILE__);
        }
        $this->dirlist($path);
        if (is_array($this->csv_file_schema) && count($this->csv_file_schema) > 0) {
            foreach ($this->csv_file_schema as $csvPath => $csvValue) {
                $this->temp_client = $csvPath;

                $this->csvLoader($csvPath . DS . $csvValue);
            }
        }
    }

    function CsvLoader($csvfilename) {
        $this->currentCsvProcess = $csvfilename;
        echo '<hr>';
        if (DEBUG) {
            echo '--------Now processing ' . $csvfilename . '------ <br/>' . "\n";
        }

        echo "<br> process for woocommerce channel <br>";
        if (strpos(dirname($this->temp_client), 'WOO') !== false) {
            if (strpos(dirname($this->temp_client), 'GALR') !== false) {
                try {
                    $request_csv_details = $this->getCsvContent();
                    $woofunctions = new WOOFUNCTIONS();
                    $config_det = parse_ini_file($this->current_ini, true);
                    $config_det = $config_det['WOO'];
                    $woofunctions->setup($config_det['consumer_secret'], $config_det['consumer_key'], $config_det['store_url']);
                    $result = $woofunctions->GALR_process($this->temp_client, $this->currentCsvProcess, $request_csv_details);

                    if (is_file($result)) {
                        $path = str_replace("ProdIn", "ProdOut", $result);
                        #transfer GALR output csv from inprogress to complete
                        @copy($result, str_replace('inprogress', 'complete', $path));
                        #remove GALR output csv from inprogress
                        @unlink($result);
                        #transfer GALR request csv from inprogress to complete
                        @copy($this->currentCsvProcess, str_replace('inprogress', 'complete', $this->currentCsvProcess));
                        if (is_file(str_replace('inprogress', 'complete', $path))) {
                            echo '<br>' . str_replace('inprogress', 'complete', $path) . " << GALR output csv generated for Woocommerce <br>";
                        }
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }

            if (strpos(dirname($this->temp_client), 'SQP') !== false) {
                try {
                    $request_csv_details = $this->getCsvContent();
                    $woofunctions = new WOOFUNCTIONS();
                    $config_det = parse_ini_file($this->current_ini, true);
                    $config_det = $config_det['WOO'];
                    $woofunctions->setup($config_det['consumer_secret'], $config_det['consumer_key'], $config_det['store_url']);
                    $result = $woofunctions->SQP_process($this->temp_client, $this->currentCsvProcess, $request_csv_details);

                    if (is_file($result)) {
                        $path = str_replace("ProdIn", "ProdOut", $result);
                        #transfer SQP output csv from inprogress to complete
                        @copy($result, str_replace('inprogress', 'complete', $path));
                        #remove SQP output csv from inprogress
                        @unlink($result);
                        #transfer SQP request csv from inprogress to complete
                        @copy($this->currentCsvProcess, str_replace('inprogress', 'complete', $this->currentCsvProcess));
                        if (is_file(str_replace('inprogress', 'complete', $path))) {
                            echo '<br>' . str_replace('inprogress', 'complete', $path) . " << SQP output csv generated for Woocommerce <br>";
                        }
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }

            if (strpos(dirname($this->temp_client), 'SLR') !== false) {
                try {
                    $request_csv_details = $this->getCsvContent();
                    $woofunctions = new WOOFUNCTIONS();
                    $config_det = parse_ini_file($this->current_ini, true);
                    $config_det = $config_det['WOO'];
                    $woofunctions->setup($config_det['consumer_secret'], $config_det['consumer_key'], $config_det['store_url']);
                    $result = $woofunctions->SLR_process($this->temp_client, $this->currentCsvProcess, $request_csv_details);

                    if (is_file($result)) {
                        $path = str_replace("ProdIn", "ProdOut", $result);
                        #transfer SLR output csv from inprogress to complete
                        @copy($result, str_replace('inprogress', 'complete', $path));
                        #remove SLR output csv from inprogress
                        @unlink($result);
                        #transfer SLR request csv from inprogress to complete
                        @copy($this->currentCsvProcess, str_replace('inprogress', 'complete', $this->currentCsvProcess));
                        if (is_file(str_replace('inprogress', 'complete', $path))) {
                            echo '<br>' . str_replace('inprogress', 'complete', $path) . " << SLR output csv generated for Woocommerce <br>";
                        }
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }

            if (strpos(dirname($this->temp_client), 'GO') !== false) {
                try {
                    $request_csv_details = $this->getCsvContent();                    
                    if ($request_csv_details[0][0] == 'GetNewOrders') {                        
                        $woofunctions = new WOOFUNCTIONS();
                        $config_det = parse_ini_file($this->current_ini, true);
                        $config_det = $config_det['WOO'];
                        $woofunctions->setup($config_det['consumer_secret'], $config_det['consumer_key'], $config_det['store_url']);
                        $result = $woofunctions->GO_process($this->temp_client, $this->currentCsvProcess, $request_csv_details);

                        if (is_file($result)) {
                            $path = str_replace("ProdIn", "ProdOut", $result);
                            #transfer SLR output csv from inprogress to complete
                            @copy($result, str_replace('inprogress', 'complete', $path));
                            #remove SLR output csv from inprogress
                            @unlink($result);
                            #transfer SLR request csv from inprogress to complete
                            @copy($this->currentCsvProcess, str_replace('inprogress', 'complete', $this->currentCsvProcess));
                            if (is_file(str_replace('inprogress', 'complete', $path))) {
                                echo '<br>' . str_replace('inprogress', 'complete', $path) . " << GO output csv generated for Woocommerce <br>";
                            }
                        }
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
    }

    function getCsvContent() {
        $action = array();
        $row = 1;
        if (($handle = fopen($this->currentCsvProcess, "r")) !== FALSE) {
            while (($csvContent = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($csvContent);
                if (DEBUG) {
                    #####echo "<p> $num fields in line $row: <br /></p>\n";
                }
                $row++;
                $action1 = array();
                for ($c = 0; $c < $num; $c++) {
                    if (DEBUG) {
                        $action1[] = $csvContent[$c];
                        if (DEBUG) {
                            #####echo $csvContent[$c] . "<br/>";
                        }
                    }
                }
                array_push($action, $action1);
            }
            fclose($handle);
        }
        return $action;
    }

    private function dirList($path) {
        $files = scandir($path);
        foreach ($files as $item) {
            if ($item == '..' || $item == '.')
                continue;

            if (is_dir($path . '/' . $item)) {
                $this->dirctory_schema[$path] = $item;
                $this->dirList($path . '/' . $item); // recurssion 
            } else {
                if (is_file($path . DS . $item)) {
                    //$this->dirctory_schema[$path] = $item;
                    if (strpos($item, ".ini") !== false) {
                        $this->current_ini = $path . DS . $item;
                    }
                    if (strpos($item, ".csv") !== false) {
                        #if(strtolower(basename(dirname($path.DS.$item)))=='inprogress'){
                        #$this->csv_file_schema[$path]=$item;//only inprogress CSV       
                        #}

                        if (strpos(strtolower(dirname($path . DS . $item)), 'prodin/go/inprogress') !== false || strpos(strtolower(dirname($path . DS . $item)), 'prodin/galr/inprogress') !== false || strpos(strtolower(dirname($path . DS . $item)), 'prodin/slr/inprogress') !== false || strpos(strtolower(dirname($path . DS . $item)), 'prodin/sqp/inprogress') !== false) {
                            $this->csv_file_schema[$path] = $item; //only inprogress CSV              
                        }
                        $this->all_csv_file_schema[$path] = $item;
                    }
                }
            }
        }
    }

    function getAllClientCsvList() {
        // all list of csv with path of directory 
    }

}

$csvHandler = new CsvParser();
$csvHandler->processCsv($panel_path);
echo 'process completed';
exit;
?>