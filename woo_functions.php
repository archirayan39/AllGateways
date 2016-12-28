<?php

require __DIR__ . '/wc-api-php-master/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

class WOOFUNCTIONS {

    private $secrete;
    private $key;
    private $store;
    private $woocommerce;

    public function setup($sc, $ky, $st) {
        $this->secrete = $sc;
        $this->key = $ky;
        $this->store = $st;
        $this->woocommerce = new Client($this->store, $this->key, $this->secrete, ['version' => 'v3',]);
    }

    public function GALR_process($temp_client, $currentCsvProcess, $request_csv_details) {
        try {
            $wooproducts = $this->woocommerce->get('products');
            $wooproducts = $wooproducts['products'];
        } catch (HttpClientException $e) {
            echo $e->getMessage(); // Error message.
            $e->getRequest(); // Last request data.
            $e->getResponse(); // Last response data.
        }

        $outputprod = array();

        if ($request_csv_details[0][0] == 'sku') {
            $updatearry = array();
            for ($r = 1; $r < count($request_csv_details); $r++) {
                if ($request_csv_details[$r][0] != '' || $request_csv_details[$r][1] != '' || $request_csv_details[$r][2] != '') {
                    try {

                        $update_avail = 'No';
                        $price = '';
                        $quantity = '';
                        for ($p = 0; $p < count($wooproducts); $p++) {
                            #echo $request_csv_details[$r][0].' >> '.$wooproducts[$p]['sku'].'<br>';
                            if ($request_csv_details[$r][0] == $wooproducts[$p]['sku']) {
                                if ($request_csv_details[$r][1] != $wooproducts[$p]['price']) {
                                    #echo $request_csv_details[$r][1].' >> '.$wooproducts[$p]['price'].'<< price diff <br>';
                                    $update_avail = 'Yes';
                                    $price = $wooproducts[$p]['price'];
                                    break;
                                }

                                if ($request_csv_details[$r][2] != $wooproducts[$p]['stock_quantity'] && $wooproducts[$p]['managing_stock'] == true) {
                                    #echo $request_csv_details[$r][2].' >> '.$wooproducts[$p]['stock_quantity'].'<< qty diff <br>';
                                    $update_avail = 'Yes';
                                    $quantity = $wooproducts[$p]['stock_quantity'];
                                    break;
                                }
                            }
                        }

                        if ($update_avail == 'Yes') {
                            if ($price == '') {
                                $price = $wooproducts[$p]['price'];
                            }
                            if ($quantity == '') {
                                $quantity = $wooproducts[$p]['stock_quantity'];
                            }
                            $updation = array("sku" => $wooproducts[$p]['sku'], "price" => $price, "quantity" => $quantity);

                            array_push($updatearry, $updation);
                        }
                    } catch (HttpClientException $e) {
                        echo $e->getMessage(); // Error message.
                        $e->getRequest(); // Last request data.
                        $e->getResponse(); // Last response data.
                    }
                }
            }
        }

        if (count($updatearry) > 0) {
            #output path cration
            $path = str_replace("ProdIn", "ProdOut", $temp_client);
            $file = fopen($path . "/GALR_RCR.csv", "w");
            #set product output csv headers name array  
            $header_array = array('sku', 'price', 'quantity');
            #set product output csv headers 
            fputcsv($file, $header_array);
            #set product output csv orders details
            if (count($updatearry) > 0) {
                for ($i = 0; $i < count($updatearry); $i++) {
                    fputcsv($file, array($updatearry[$i]['sku'], $updatearry[$i]['price'], $updatearry[$i]['quantity']));
                }
            }
            return $path . "/GALR_RCR.csv";
        } else {
            return 'error';
        }
    }

    public function SQP_process($temp_client, $currentCsvProcess, $request_csv_details) {
        try {
            $wooproducts = $this->woocommerce->get('products');
            $wooproducts = $wooproducts['products'];
        } catch (HttpClientException $e) {
            echo $e->getMessage(); // Error message.
            $e->getRequest(); // Last request data.
            $e->getResponse(); // Last response data.
        }

        $outputprod = array();

        if ($request_csv_details[0][0] == 'sku') {
            $updatearry = array();
            for ($r = 1; $r < count($request_csv_details); $r++) {
                if ($request_csv_details[$r][0] != '' || $request_csv_details[$r][1] != '' || $request_csv_details[$r][2] != '') {
                    try {

                        $update_avail = 'No';
                        $price = '';
                        $quantity = '';
                        for ($p = 0; $p < count($wooproducts); $p++) {
                            #echo $request_csv_details[$r][0].' >> '.$wooproducts[$p]['sku'].'<br>';
                            if ($request_csv_details[$r][0] == $wooproducts[$p]['sku']) {
                                $data = array("regular_price" => $request_csv_details[$r][1], "stock_quantity" => $request_csv_details[$r][2], 'managing_stock' => true);
                                $updation = $this->woocommerce->put('products/' . $wooproducts[$p]['id'], array("product" => $data));
                                $updation = $updation['product'];
                                $upd = array("sku" => $updation['sku'], "price" => $updation['price'], "quantity" => $updation['stock_quantity']);
                                array_push($updatearry, $upd);
                                break;
                            }
                        }
                    } catch (HttpClientException $e) {
                        echo $e->getMessage(); // Error message.
                        $e->getRequest(); // Last request data.
                        $e->getResponse(); // Last response data.
                    }
                }
            }
        }


        if (count($updatearry) > 0) {
            #output path cration
            $path = str_replace("ProdIn", "ProdOut", $temp_client);
            $file = fopen($path . "/SQP_RCR.csv", "w");
            #set product output csv headers name array  
            $header_array = array('sku', 'price', 'quantity', 'gw_result');
            #set product output csv headers 
            fputcsv($file, $header_array);
            #set product output csv orders details
            if (count($updatearry) > 0) {
                for ($i = 0; $i < count($updatearry); $i++) {
                    fputcsv($file, array($updatearry[$i]['sku'], $updatearry[$i]['price'], $updatearry[$i]['quantity'], 'success'));
                }
            }
            return $path . "/SQP_RCR.csv";
        } else {
            return 'error';
        }
    }

    public function SLR_process($temp_client, $currentCsvProcess, $request_csv_details) {
        try {
            $wooproducts = $this->woocommerce->get('products');
            $wooproducts = $wooproducts['products'];
        } catch (HttpClientException $e) {
            echo $e->getMessage(); // Error message.
            $e->getRequest(); // Last request data.
            $e->getResponse(); // Last response data.
        }

        $outputprod = array();

        for ($r = 1; $r < count($request_csv_details); $r++) {
            if ($request_csv_details[$r][0] != '' || $request_csv_details[$r][1] != '') {
                try {
                    if ($request_csv_details[$r][2] == '' || $request_csv_details[$r][3] == '' || $request_csv_details[$r][4] == '') {
                        $status = 'draft';
                    } else {
                        $status = 'publish';
                    }

                    if ($request_csv_details[$r][0] != '') {
                        $sku = $request_csv_details[$r][0];
                        $type = 'simple';
                    } else {
                        $sku = $request_csv_details[$r][1];
                        $type = 'variable';
                    }

                    if ($request_csv_details[$r][2] != '') {
                        $catidarr = array();
                        $cats = explode('|', $request_csv_details[$r][2]);
                        #checking categories on woocommerce
                        for ($c = 0; $c < count($cats); $c++) {
                            $availinw = 'No';
                            $woo_avail_cats = $this->woocommerce->get('products/categories');
                            $woo_avail_cats = $woo_avail_cats['product_categories'];
                            if (count($woo_avail_cats) > 0) {
                                for ($cw = 0; $cw < count($woo_avail_cats); $cw++) {
                                    if ($cats[$c] == $woo_avail_cats[$cw]['name']) {
                                        array_push($catidarr, $woo_avail_cats[$cw]['id']);
                                        $availinw = 'Yes';
                                        break;
                                    }
                                }
                            }

                            if ($availinw == 'No') {
                                try {
                                    $inserted_cat = $this->woocommerce->post('products/categories', array('product_category' => array("name" => $cats[$c])));
                                    array_push($catidarr, $inserted_cat['product_category']['id']);
                                } catch (HttpClientException $e) {
                                    echo $e->getMessage(); // Error message.
                                    $e->getRequest(); // Last request data.
                                    $e->getResponse(); // Last response data.
                                }
                            }
                        }
                    }

                    if ($request_csv_details[$r][9] != '') {
                        $images = explode('|', $request_csv_details[$r][9]);
                        if (count($images) > 0) {
                            $imgarr = array();
                            for ($im = 0; $im < count($images); $im++) {
                                if ($images[$im] != '') {
                                    $newarr['src'] = $images[$im];
                                    $newarr['position'] = $im;
                                    array_push($imgarr, $newarr);
                                }
                            }
                        }
                    }

                    $attributes = array();
                    $defattributes = array();
                    $variation = array();

                    if ($request_csv_details[$r][6] != '') {
                        $attributesinfo = explode('|', $request_csv_details[$r][6]);

                        if (count($attributesinfo) > 0) {
                            for ($at = 0; $at < count($attributesinfo); $at++) {
                                if ($attributesinfo[$at] != '') {
                                    $attrdet = explode('=', $attributesinfo[$at]);
                                    $opt = explode(';', $attrdet[1]);
                                    if (count($opt) > 0) {
                                        $nwarray['name'] = $attrdet[0];
                                        $nwarray['slug'] = strtolower($attrdet[0]);
                                        $nwarray['position'] = $at;
                                        $nwarray['options'] = $opt;
                                        $nwarray['visible'] = false;
                                        $nwarray['variation'] = true;
                                        array_push($attributes, $nwarray);

                                        $nwarray1['name'] = $attrdet[0];
                                        $nwarray1['slug'] = strtolower($attrdet[0]);
                                        $nwarray1['options'] = $opt;

                                        if ($at == 0) {
                                            array_push($defattributes, $nwarray1);
                                        }

                                        $nwarry2['regular_price'] = $request_csv_details[$r][4];
                                        $nwarry2['attributes'] = $nwarray1;
                                        array_push($variation, $nwarry2);
                                    }
                                }
                            }
                        }
                    }

                    $product = array(
                        'title' => $request_csv_details[$r][3],
                        'sku' => $sku,
                        'regular_price' => $request_csv_details[$r][4],
                        'type' => $type,
                        'description' => $request_csv_details[$r][7],
                        'status' => $status,
                        "categories" => $catidarr,
                        "images" => $imgarr,
                        "attributes" => $attributes,
                        "default_attributes" => $defattributes,
                        "variations" => $variation,
                        "managing_stock" => true,
                        "stock_quantity" => $request_csv_details[$r][5],
                        "short_description" => $request_csv_details[$r][7],
                        "description" => $request_csv_details[$r][8],
                        "dimensions" => array("length" => $request_csv_details[$r][10], "width" => $request_csv_details[$r][11], "height" => $request_csv_details[$r][12])
                    );

                    $woo_prod_avail = 'No';

                    for ($woop = 0; $woop < count($wooproducts); $woop++) {
                        if ($wooproducts[$woop]['sku'] == $request_csv_details[$r][0]) {
                            $woo_prod_avail = 'Yes';
                            break;
                        }
                    }

                    if ($woo_prod_avail == 'No') {
                        $inserted = $this->woocommerce->post('products', array("product" => $product));
                        array_push($outputprod, $inserted['product']);
                    }
                } catch (HttpClientException $e) {
                    echo $e->getMessage(); // Error message.
                    $e->getRequest(); // Last request data.
                    $e->getResponse(); // Last response data.
                }
            }
        }

        if (count($outputprod) > 0) {
            #output path cration
            $path = str_replace("ProdIn", "ProdOut", $temp_client);
            $file = fopen($path . "/SLR_RCR.csv", "w");
            #set product output csv headers name array  
            $header_array = array('sku', 'variation-sku', 'title', 'DateTimeListed', 'slr_result', 'sku-permalink', 'variation-sku-permalink');
            #set product output csv headers 
            fputcsv($file, $header_array);
            #set product output csv orders details
            if (count($outputprod) > 0) {
                for ($i = 0; $i < count($outputprod); $i++) {
                    fputcsv($file, array($outputprod[$i]['sku'], '', $outputprod[$i]['title'], $outputprod[$i]['created_at'], $outputprod[$i]['id'], $outputprod[$i]['permalink'], ''));
                }
            }
            return $path . "/SLR_RCR.csv";
        } else {
            return 'error';
        }
    }

    public function GO_process($temp_client, $currentCsvProcess, $request_csv_details) {
        try {            
            $orders = $this->woocommerce->get('orders');
            $orders_details = $orders['orders'];
        } catch (HttpClientException $e) {
            echo $e->getMessage(); // Error message.
            $e->getRequest(); // Last request data.
            $e->getResponse(); // Last response data.
        }
        
        if (count($orders_details) > 0) {
            #output path cration            
            $path = str_replace("ProdIn", "ProdOut", $temp_client);
            #set orders output csv
            $file = fopen($path . "/GO_ICR.csv", "w");
            #set orders output csv headers name array  
            $header_array = array('order-id', 'order-item-id', 'purchase-date', 'payments-date', 'buyer-email', 'buyer-name', 'buyer-phone-number', 'sku', 'variation-sku', 'product-name', 'quantity-purchased', 'currency', 'item-price', 'item-tax', 'shipping-price', 'shipping-tax', 'ship-service-level', 'recipient-name', 'ship-address-1', 'ship-address-2', 'ship-address-3', 'ship-city', 'ship-state', 'ship-postal-code', 'ship-country', 'ship-phone-number', 'delivery-start-date', 'delivery-end-date', 'delivery-time-zone', 'delivery-Instructions', 'sales-channel');
            #set orders output csv headers 
            fputcsv($file, $header_array);
            #set orders output csv orders details
            
            for($o=0;$o<count($orders_details);$o++){
                $order_items = $orders_details[$o]['line_items'];
                if (count($order_items) > 0) {
                    for($oi=0;$oi<count($order_items);$oi++){
                        fputcsv($file, array($orders_details[$o]['id'], $order_items[$oi]['id'], $orders_details[$o]['created_at'], $order_items[$oi]['created_at'], $orders_details[$o]['billing_address']['email'], $orders_details[$o]['billing_address']['first_name'], $orders_details[$o]['billing_address']['phone'], $order_items[$oi]['sku'], '', $order_items[$oi]['name'], $order_items[$oi]['quantity'], $orders_details[$o]['currency'], $order_items[$oi]['price'], $order_items[$oi]['total_tax'], '', '', '', $orders_details[$o]['shipping_address']['first_name'], $orders_details[$o]['shipping_address']['address_1'], $orders_details[$o]['shipping_address']['address_2'], '', $orders_details[$o]['shipping_address']['city'], $orders_details[$o]['shipping_address']['state'], $orders_details[$o]['shipping_address']['postcode'], $orders_details[$o]['shipping_address']['country'], $orders_details[$o]['shipping_address']['phone'], '', '', '', $orders_details[$o]['note'], 'Woocommerce'));
                    }
                }
            }
            
//            foreach ($orders_details as $orderraw) {
//                $order_items = $orderraw->line_items;
//                if (count($order_items) > 0) {
//                    foreach ($order_items as $itemraw) {
//                        //fputcsv($file, array($orderraw->id, $itemraw->id, $orderraw->created_at, $orderraw->created_at, $orderraw->billing_address->email, $orderraw->billing_address->first_name, $orderraw->billing_address->phone, $itemraw->sku, 'variation-sku', $itemraw->name, $itemraw->quantity, $orderraw->currency, $itemraw->price, $itemraw->total_tax, '', '', '', $orderraw->shipping_address->first_name, $orderraw->shipping_address->address_1, $orderraw->shipping_address->address_2, '', $orderraw->shipping_address->city, $orderraw->shipping_address->state, $orderraw->shipping_address->postcode, $orderraw->shipping_address->country, $orderraw->shipping_address->phone, '', '', '', $orderraw->note, 'Woocommerce'));
//                    }
//                }
//            }
            return $path . "/GO_ICR.csv";
        }else{
            return 'error';
        }
    }

}

?>