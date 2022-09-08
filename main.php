<?php
/*

Plugin Name: CakeShopPlugin
Description: A plugin for cakeshop api
Version: 1.0.0
Author: Abeid
Author URI: https://github.com/ab3id

*/

class CakeShop
{
    protected $api_url = 'https://dhukitech.co.tz/cakeshop-api/ci/public/client/api';
    protected $access_token;
    protected $api_response;

    function __construct()
    {
        add_action('admin_menu', array($this, 'CreateSettingsLink'));
        add_action('admin_init', array($this, 'Settings'));
        add_shortcode('cshp_shortcode', array($this, 'cakeshopWpBody'));
        $this->access_token = get_option('cshp_api_key');
        $this->makeApiRequest('');
    }


    

    function makeApiRequest($filter)
    {
        $response = "";
      
        if($filter !== ""){
            $response = wp_remote_post( $this->api_url.'/product/search', array(
                'method'      => 'POST',
                'headers'     => array('API_TOKEN' => $this->access_token),
                'body'        => array(
                    'search_input' => $filter,
                ),
         
                )
            );
        }else{
            $response = wp_remote_get(
                $this->api_url . '/products',
                array(
                    'timeout' => 10,
                    'headers' => array('API_TOKEN' => $this->access_token)
                )
            );
        }

        
     
        try {
            $this->api_response = json_decode($response['body']);
        } catch (Exception $e) {
            $this->api_response = null;
        }

        return $this->api_response;
    }

    function Settings()
    {
        add_settings_section('cshp_first_section', null, array($this, 'pluginRegistrationNotice'), 'cake-shop-settings');


        add_settings_field('cshp_api_key', 'API Key', array($this, 'ApiKeyHtml'), 'cake-shop-settings', 'cshp_first_section');
        register_setting('cshpsettings', 'cshp_api_key', array('type' => 'string', 'description' => 'Cakeshop API Access Token', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'NA'));
    }

    function pluginRegistrationNotice()
    {
        if (get_option('cshp_api_key') == 'NA') {
?> <span>Plugin Not Registered</span> <?php
                                    } else { ?> <span>Plugin Registered Successfully</span> <?php }
                                                                                    }

                                                                                    function cakeshopWpBody($attributes)
                                                                                    
                                                                                    {
                                                                                        extract(
                                                                                            shortcode_atts(
                                                                                                array(
                                                                                                    'type' => '',
                                                                                                ),
                                                                                                $attributes
                                                                                            )
                                                                                        );
                                                                                        
                                                                                        if($type !== ''){
                                                                                            
                                                                                            $this->makeApiRequest($type);
                                                                                        }
                                                                                            ?>
        <style>
            .main_body {
                width: 100%;
                min-height: 100vh;

                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;

            }

            .body_content {
                width: 50%;
                min-height: 100vh;

                display: flex;
                flex-direction: column;
            }

            .card_item {
                border-radius: 5px;
                background-color: white;
                margin: 10px;
                cursor: pointer;
            }

            .content_container {
                padding: 2px 16px;
            }

            .desc p {
                margin: 0px;
                padding: 0px;
            }

            .mb_2 {
                margin-bottom: 10px !important;
            }

            .bold {
                font-weight: 600;
            }

            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                padding-top: 100px;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgb(0, 0, 0);
                background-color: rgba(0, 0, 0, 0.4);
            }

            .cake-modal-content {
                background-color: #fefefe;
                margin: auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
            }

            .close {
                color: #aaaaaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }

            .price_section {
                margin: 5px;
                background-color: #1976D2;
                padding: 6px;
                color: #fff;
                border-radius: 5px;
                cursor: pointer;
                width: 20%;
                text-align: center;
            }

            .ct {
                display: flex;
                width: 100%;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
        </style>


        <section>
            <div class="main_body">
                <div class="body_content">

                    <?php
                    
                      
                                                                                        if ($this->api_response->cakes !== null) {
                                                                                            foreach ($this->api_response->cakes as $item) {

                    ?>
                            <div class="card_item" onclick="showModal('<?php echo htmlspecialchars(trim($item->name)); ?>','<?php echo htmlspecialchars(trim($item->recipe)); ?>','<?php echo htmlspecialchars(trim($item->price)); ?>')">
                                <div class="content_container">
                                    <h4><b><?php echo $item->name ?></b></h4>
                                    <div class="desc">
                                        <p class="bold">Recipe </p>
                                        <p class="mb_2"><?php echo $item->recipe ?></p>
                                    </div>
                                </div>
                            </div>
                    <?php
                                                                                            }
                                                                                        } else {

                                                                                            echo "<center>" . $this->api_response->error . "</center>";
                                                                                        }

                    ?>

                </div>
            </div>

            <div id="cakeDetailsModal" class="modal">


                <div class="cake-modal-content">
                    <span class="close">&times;</span>

                    <div class="content_container">
                        <h4 id="cake_name"></h4>
                        <div class="desc">
                            <p class="bold">Recipe </p>
                            <p class="mb_2" id="cake_recipe"></p>
                        </div>
                        <div class="ct">
                            <div class="price_section" onclick="alert('Price '+cake_price)">Purchase</div>
                        </div>
                    </div>
                </div>

            </div>

            <script>
                var modal = document.getElementById("cakeDetailsModal");


                var cake_name = document.getElementById("cake_name");
                var cake_recipe = document.getElementById("cake_recipe");


                var span = document.getElementsByClassName("close")[0];

                var cake_price = 0;


                function showModal(name, recipe, price) {
                    cake_name.innerHTML = name;
                    cake_recipe.innerHTML = recipe;
                    cake_price = price;
                    modal.style.display = "block";
                }


                span.onclick = function() {
                    modal.style.display = "none";
                }


                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            </script>
        </section>
    <?php
                                                                                    }



                                                                                    function ApiKeyHtml()
                                                                                    { ?>
        <style>
            .clearKey {
                color: #b32d2e;
                cursor: pointer;
            }
        </style>

        <input id="keyInput" <?php echo get_option('cshp_api_key') == 'NA' ? 'false' : 'disabled' ?> type="<?php echo get_option('cshp_api_key') == 'NA' ? 'text' : 'password' ?>" name="cshp_api_key" value="<?php echo  get_option('cshp_api_key') == 'NA' ?  esc_attr(get_option('cshp_api_key')) : 'hidden' ?>">

        <span class="clearKey" onclick="clearInput()">Clear</span>


        <script>
            const input = document.getElementById('keyInput');

            function clearInput() {
                input.removeAttribute('disabled');
                input.setAttribute('value', 'NA');
                input.setAttribute('type', 'text');
            }
        </script>
    <? }

                                                                                    function CreateSettingsLink()
                                                                                    {
                                                                                        add_options_page('Cake Shop Settings', 'CakeShop', 'manage_options', 'cake-shop-settings', array($this, 'GenerateSettingsView'), 1);
                                                                                    }

                                                                                    function GenerateSettingsView()
                                                                                    { ?>
        <div class="wrap">
            <h1>Cake Shop Api Settings</h1>
            <form action="options.php" method="POST">
                <?php
                                                                                        settings_fields('cshpsettings');
                                                                                        do_settings_sections('cake-shop-settings');
                                                                                        submit_button();

                ?>
            </form>
        </div>
<? }
                                                                                }

                                                                                $shop = new CakeShop();
