<?php
/**
 * Plugin Name: Weather Widget Generator
 * Plugin URI: https://example.com/weather-widget
 * Description: Tạo widget thời tiết có thể kéo thả trong page builder
 * Version: 1.0.0
 * Author: Nguyên Khôi
 * Author URI: https://nguyenkhoi.dev/
 * Text Domain: weather-widget-generator
 */

if (!defined('ABSPATH')) exit;

class Weather_Widget_Generator {
    
    public function __construct() {
        // Register widget
        add_action('widgets_init', array($this, 'register_widget'));
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Ajax handlers
        add_action('wp_ajax_get_weather', array($this, 'get_weather_data'));
        add_action('wp_ajax_nopriv_get_weather', array($this, 'get_weather_data'));
        
        // Shortcode
        add_shortcode('weather_widget_generator', array($this, 'widget_generator_shortcode'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add rewrite rules for iframe endpoint
        add_action('init', array($this, 'add_iframe_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_iframe_query_vars'));
        add_action('parse_request', array($this, 'handle_iframe_request'));
    }
    
    public function register_widget() {
        register_widget('Weather_Widget_Generator_Widget');
    }
    
    public function enqueue_scripts() {
        
        wp_enqueue_script('weather-widget-js', plugins_url('assets/weather-widget.js', __FILE__), array('jquery'), '1.0.0', true);
        wp_localize_script('weather-widget-js', 'weatherWidget', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('weather_widget_nonce')
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        wp_enqueue_style('weather-widget-admin-css', plugins_url('assets/admin-style.css', __FILE__), array(), '1.0.0');
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Weather Widget',
            'Weather Widget',
            'manage_options',
            'weather-widget-generator',
            array($this, 'admin_page'),
            'dashicons-cloud',
            30
        );
    }
    
    public function admin_page() {
        ?>
        <div class="wrap weather-admin-wrap">
            <h1>Weather Widget Generator</h1>
            <p>Sử dụng widget này trong Appearance → Widgets hoặc trong Page Builder</p>
            <p>Hoặc sử dụng shortcode: <code>[weather_widget_generator]</code></p>
            
            <div class="weather-admin-instructions">
                <h2>Hướng dẫn sử dụng:</h2>
                <ol>
                    <li><strong>Trong Widget:</strong> Vào Appearance → Widgets, kéo "Weather Widget Generator" vào sidebar</li>
                    <li><strong>Trong Page Builder:</strong> Tìm widget "Weather Widget Generator" và kéo vào trang</li>
                    <li><strong>Shortcode:</strong> Sử dụng <code>[weather_widget_generator city="ha-noi" days="3"]</code></li>
                </ol>
                
                <h3>Shortcode Parameters:</h3>
                <ul>
                    <li><code>city</code> - Slug của thành phố (mặc định: ha-noi)</li>
                    <li><code>days</code> - Số ngày dự báo: 3, 5, 7 (mặc định: 3)</li>
                    <li><code>width</code> - Chiều rộng widget (mặc định: 500)</li>
                    <li><code>header_color</code> - Màu chữ tiêu đề (mặc định: #ffffff)</li>
                    <li><code>bg_header_color</code> - Màu nền tiêu đề (mặc định: #16a34a)</li>
                    <li><code>text_color</code> - Màu chữ nội dung (mặc định: #000000)</li>
                    <li><code>border_color</code> - Màu viền (mặc định: #16a34a)</li>
                </ul>
                
                <h3>Ví dụ:</h3>
                <code>[weather_widget_generator city="ha-noi" days="5" width="600" bg_header_color="#0066cc"]</code>
            </div>
        </div>
        <style>
            .weather-admin-wrap {
                max-width: 900px;
            }
            .weather-admin-instructions {
                background: #fff;
                padding: 30px;
                margin-top: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            .weather-admin-instructions h2 {
                margin-top: 0;
                color: #2c3e50;
            }
            .weather-admin-instructions h3 {
                margin-top: 25px;
                color: #34495e;
            }
            .weather-admin-instructions ul,
            .weather-admin-instructions ol {
                margin-left: 20px;
                line-height: 1.8;
            }
            .weather-admin-instructions code {
                background: #f5f5f5;
                padding: 3px 8px;
                border-radius: 4px;
                font-size: 13px;
            }
        </style>
        <?php
    }
    
    public function widget_generator_shortcode($atts) {
        $atts = shortcode_atts(array(
            'city' => 'ha-noi',
            'days' => '3',
            'width' => '500',
            'header_color' => '#ffffff',
            'bg_header_color' => '#16a34a',
            'text_color' => '#000000',
            'border_color' => '#16a34a',
            'line_color' => '#dddddd',
        ), $atts);
        
        return $this->render_widget_generator($atts);
    }
    
    public function render_widget_generator($settings) {
        $city_slug = $settings['city'];
        $days = intval($settings['days']);
        $width = intval($settings['width']);
        
        // Get city name
        $city_term = get_term_by('slug', $city_slug, 'city');
        $city_name = $city_term ? $city_term->name : 'Hà Nội';
        
        // Get all cities for dropdown
        $cities = get_terms(array(
            'taxonomy' => 'city',
            'hide_empty' => false,
        ));
        
        ob_start();
        ?>
        <div class="weather-widget-generator-wrapper">
            <div class="weather-settings-panel" style="max-width: <?php echo esc_attr($width); ?>px;width:100%; margin: 0 auto;">
                <h3>Tùy chỉnh Widget Thời Tiết</h3>
                
                <div class="weather-form-group">
                    <label>Địa điểm:</label>
                    <select class="weather-city-select" data-target="preview-<?php echo uniqid(); ?>">
                        <?php 
                        if (!empty($cities) && !is_wp_error($cities)) {
                            foreach ($cities as $city) {
                                $selected = ($city->slug == $city_slug) ? 'selected' : '';
                                echo '<option value="' . esc_attr($city->slug) . '" ' . $selected . '>' . esc_html($city->name) . '</option>';
                            }
                        } else {
                            echo '<option value="ha-noi">Hà Nội</option>';
                            echo '<option value="ho-chi-minh">Hồ Chí Minh</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="weather-form-group">
                    <label>Kích thước: <span class="weather-width-value"><?php echo $width; ?>px</span></label>
                    <input type="range" class="weather-width-slider" min="300" max="800" value="<?php echo $width; ?>" step="10">
                </div>
                
                <div class="weather-form-group">
                    <label>Số ngày dự báo:</label>
                    <div class="weather-days-btns">
                        <button type="button" class="weather-day-btn <?php echo $days == 3 ? 'active' : ''; ?>" data-days="3">3 ngày</button>
                        <button type="button" class="weather-day-btn <?php echo $days == 5 ? 'active' : ''; ?>" data-days="5">5 ngày</button>
                        <button type="button" class="weather-day-btn <?php echo $days == 7 ? 'active' : ''; ?>" data-days="7">7 ngày</button>
                    </div>
                </div>
                
                <div class="weather-form-group">
                    <label>Màu sắc:</label>
                    <div class="weather-color-grid">
                        <div class="weather-color-item">
                            <label>Chữ tiêu đề</label>
                            <input type="color" class="weather-header-color" value="<?php echo esc_attr($settings['header_color']); ?>">
                        </div>
                        <div class="weather-color-item">
                            <label>Nền tiêu đề</label>
                            <input type="color" class="weather-bg-header-color" value="<?php echo esc_attr($settings['bg_header_color']); ?>">
                        </div>
                        <div class="weather-color-item">
                            <label>Văn bản</label>
                            <input type="color" class="weather-text-color" value="<?php echo esc_attr($settings['text_color']); ?>">
                        </div>
                        <div class="weather-color-item">
                            <label>Viền</label>
                            <input type="color" class="weather-border-color" value="<?php echo esc_attr($settings['border_color']); ?>">
                        </div>
                    </div>
                </div>
                
                <button type="button" class="weather-generate-btn">🔄 Cập nhật Preview</button>
                
                <div class="weather-form-group" style="margin-top: 20px;">
                    <label>Mã nhúng:</label>
                    <textarea class="weather-embed-code" readonly rows="4"><iframe src="<?= home_url('/iframe/?city=ha-noi&days=3')?>" width="100%" height="400" frameborder="0" style="border:none;"></iframe></textarea>
                    <button type="button" class="weather-copy-btn">📋 Copy Code</button>
                </div>
            </div>
            
            <div class="weather-preview-wrapper" id="preview-<?php echo uniqid(); ?>" style="max-width: <?php echo esc_attr($width); ?>px;width: 100%; margin: 0 auto;">
                <div class="weather-widget-preview" 
                     style="border: 2px solid <?php echo esc_attr($settings['border_color']); ?>; border-radius: 8px; overflow: hidden;">
                    <div class="weather-header" 
                         style="background: <?php echo esc_attr($settings['bg_header_color']); ?>; color: <?php echo esc_attr($settings['header_color']); ?>; padding: 15px; font-weight: bold; text-align: center;">
                        Thời tiết <span class="city-name"><?php echo esc_html($city_name); ?></span> hôm nay
                    </div>
                    <div class="weather-content" 
                         style="padding: 20px; background: #fff; color: <?php echo esc_attr($settings['text_color']); ?>;">
                        <div class="weather-loading" style="text-align: center; padding: 40px;">
                            Đang tải dữ liệu thời tiết...
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .weather-widget-generator-wrapper {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                display: flex;
            }
            @media screen and (max-width: 768px)
                {
                    .weather-widget-generator-wrapper {
                        flex-direction: column;
                        gap:20px;
                    }
                }
            .weather-settings-panel {
                background: #fff;
                padding: 25px;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            }
            .weather-settings-panel h3 {
                margin-top: 0;
                margin-bottom: 20px;
                color: #2c3e50;
                font-size: 20px;
            }
            .weather-form-group {
                margin-bottom: 20px;
            }
            .weather-form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                font-size: 14px;
                color: #2c3e50;
            }
            .weather-form-group select,
            .weather-form-group input[type="text"] {
                width: 100%;
                min-height: 50px;
                padding: 10px;
                border: 2px solid #e1e8ed;
                border-radius: 6px;
                font-size: 14px;
            }
            .weather-form-group input[type="range"] {
                width: 100%;
            }
            .weather-width-value {
                float: right;
                color: #3498db;
                font-weight: 600;
            }
            .weather-days-btns {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
            .weather-day-btn {
                padding: 10px;
                border: 2px solid #e1e8ed;
                background: #fff;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s;
            }
            .weather-day-btn:hover {
                border-color: #3498db;
            }
            .weather-day-btn.active {
                background: #3498db;
                color: #fff;
                border-color: #3498db;
            }
            .weather-color-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            .weather-color-item label {
                font-size: 12px;
                margin-bottom: 5px;
            }
            .weather-color-item input[type="color"] {
                width: 100%;
                height: 45px;
                border: 2px solid #e1e8ed;
                border-radius: 6px;
                cursor: pointer;
            }
            .weather-generate-btn {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
                font-size: 15px;
                transition: all 0.3s;
            }
            .weather-generate-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }
            .weather-embed-code {
                width: 100%;
                padding: 12px;
                border: 2px solid #e1e8ed;
                border-radius: 6px;
                font-family: monospace;
                font-size: 12px;
                resize: vertical;
            }
            .weather-copy-btn {
                margin-top: 10px;
                padding: 10px 20px;
                background: #3498db;
                color: #fff;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 600;
            }
            .weather-copy-btn:hover {
                background: #2980b9;
            }
            .weather-preview-wrapper {
                margin-top: 30px;
            }
        </style>
        
        <script defer>
            jQuery(document).ready(function($) {
                var $wrapper = $('.weather-widget-generator-wrapper').last();
                
                // Width slider
                $wrapper.find('.weather-width-slider').on('input', function() {
                    var width = $(this).val();
                    $wrapper.find('.weather-width-value').text(width + 'px');
                    $wrapper.find('.weather-preview-wrapper').css('max-width', width + 'px');
                });
                
                // Day buttons
                $wrapper.find('.weather-day-btn').on('click', function() {
                    $wrapper.find('.weather-day-btn').removeClass('active');
                    $(this).addClass('active');
                });
                
                // Generate/Update button
                $wrapper.find('.weather-generate-btn').on('click', function() {
                    var city = $wrapper.find('.weather-city-select').val();
                    var cityName = $wrapper.find('.weather-city-select option:selected').text();
                    var days = $wrapper.find('.weather-day-btn.active').data('days');
                    var width = $wrapper.find('.weather-width-slider').val();
                    var headerColor = $wrapper.find('.weather-header-color').val();
                    var bgHeaderColor = $wrapper.find('.weather-bg-header-color').val();
                    var textColor = $wrapper.find('.weather-text-color').val();
                    var borderColor = $wrapper.find('.weather-border-color').val();
                    
                    // Update colors
                    $wrapper.find('.weather-widget-preview').css('border-color', borderColor);
                    $wrapper.find('.weather-header').css({
                        'background': bgHeaderColor,
                        'color': headerColor
                    });
                    $wrapper.find('.weather-content').css('color', textColor);
                    $wrapper.find('.city-name').text(cityName);
                    
                    // Generate embed code with colors
                    var embedCode = '<iframe src="<?php echo home_url('/iframe/'); ?>?city=' + city + '&days=' + days + 
                        '&header_color=' + encodeURIComponent(headerColor) + 
                        '&bg_header_color=' + encodeURIComponent(bgHeaderColor) + 
                        '&text_color=' + encodeURIComponent(textColor) + 
                        '&border_color=' + encodeURIComponent(borderColor) + 
                        '" width="100%" height="400" frameborder="0" style="border:none;"></iframe>';
                    $wrapper.find('.weather-embed-code').val(embedCode);
                    
                    // Load weather data
                    loadWeatherData(city, $wrapper);
                });
                
                // Copy button
                $wrapper.find('.weather-copy-btn').on('click', function() {
                    var $textarea = $wrapper.find('.weather-embed-code');
                    $textarea.select();
                    document.execCommand('copy');
                    $(this).text('✓ Đã copy!');
                    setTimeout(() => {
                        $(this).text('📋 Copy Code');
                    }, 2000);
                });
                
                function loadWeatherData(city, $wrap) {
                    $.ajax({
                        url: weatherWidget.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'get_weather',
                            nonce: weatherWidget.nonce,
                            city: city
                        },
                        success: function(response) {
                            if (response.success) {
                                renderWeather(response.data, $wrap);
                            }
                        }
                    });
                }
                
                function renderWeather(data, $wrap) {
                    var days = $wrap.find('.weather-day-btn.active').data('days') || 3;
                    var html = '<div style="text-align: center;">';
                    html += '<div style="font-size: 48px; font-weight: bold; margin-bottom: 10px;">' + data.temperature + '°C</div>';
                    html += '<div style="font-size: 16px; margin-bottom: 20px;">' + data.condition + '</div>';
                    html += '<div style="display: grid; grid-template-rows: repeat(' + Math.min(days, 3) + ', 1fr); gap: 15px; padding-top: 20px; border-top: 1px solid #ddd;">';
                    
                    for (var i = 0; i < Math.min(days, data.forecast.length); i++) {
                        var item = data.forecast[i];
                        html += '<div style="padding: 10px;display: flex;justify-content: flex-start;gap:20px;align-items: center;">';
                        html += '<div style="font-size: 16px; margin-bottom: 8px;width:15%">' + item.day + '<br>' + item.date + '</div>';
                        html += '<div style="font-size: 40px;width:15%">☀️</div>';
                        html += '<div style="font-size: 16px;width:20%">' + item.temp_min + '° | ' + item.temp_max + '°</div>';
                        html += '<div style="font-size: 16px; color: #666; margin-top: 5px;">' + item.condition + '</div>';
                        html += '</div>';
                    }
                    
                    html += '</div></div>';
                    $wrap.find('.weather-content').html(html);
                }
                
                // Initial load
                loadWeatherData('<?php echo esc_js($city_slug); ?>', $wrapper);
            });
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function add_iframe_rewrite_rules() {
        add_rewrite_rule('^iframe/?', 'index.php?weather_iframe=1', 'top');
        add_rewrite_tag('%weather_iframe%', '1');
    }
    
    public function add_iframe_query_vars($vars) {
        $vars[] = 'weather_iframe';
        return $vars;
    }
    
    public function handle_iframe_request($wp) {
        if (isset($wp->query_vars['weather_iframe']) && $wp->query_vars['weather_iframe'] == '1') {
            $city = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : 'ha-noi';
            $days = isset($_GET['days']) ? sanitize_text_field($_GET['days']) : '3';
            $header_color = isset($_GET['header_color']) ? sanitize_hex_color($_GET['header_color']) : '#ffffff';
            $bg_header_color = isset($_GET['bg_header_color']) ? sanitize_hex_color($_GET['bg_header_color']) : '#16a34a';
            $text_color = isset($_GET['text_color']) ? sanitize_hex_color($_GET['text_color']) : '#000000';
            $border_color = isset($_GET['border_color']) ? sanitize_hex_color($_GET['border_color']) : '#16a34a';
            
            $this->render_iframe_template($city, $days, $header_color, $bg_header_color, $text_color, $border_color);
            exit;
        }
    }
    
    public function render_iframe_template($city_slug, $days, $header_color = '#ffffff', $bg_header_color = '#16a34a', $text_color = '#000000', $border_color = '#16a34a') {
        $city_term = get_term_by('slug', $city_slug, 'city');
        $city_name = $city_term ? $city_term->name : 'Hà Nội';
        
        // Get weather data
        $weather_data = $this->get_sample_weather_data($city_name);
        
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Thời tiết <?php echo esc_html($city_name); ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                    background: #fff;
                }
                .weather-widget {
                    border: 2px solid <?php echo esc_attr($border_color); ?>;
                    border-radius: 8px;
                    overflow: hidden;
                }
                .weather-header {
                    background: <?php echo esc_attr($bg_header_color); ?>;
                    color: <?php echo esc_attr($header_color); ?>;
                    padding: 15px;
                    font-weight: bold;
                    text-align: center;
                    font-size: 18px;
                }
                .weather-content {
                    padding: 20px;
                    background: #fff;
                    color: <?php echo esc_attr($text_color); ?>;
                }
                .weather-current {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .weather-temp {
                    font-size: 48px;
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .weather-condition {
                    font-size: 16px;
                    margin-bottom: 20px;
                }
                .weather-forecast {
                    display: grid;
                    grid-template-rows: repeat(<?php echo min(intval($days), 7); ?>, 1fr);
                    gap: 15px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }
                .forecast-item {
                    padding: 10px;
                    display: flex;
                    justify-content: flex-start;
                    gap: 20px;
                    align-items: center;
                }
                .forecast-day {
                    font-size: 16px;
                    width: 15%;
                }
                .forecast-icon {
                    font-size: 40px;
                    width: 15%;
                }
                .forecast-temp {
                    font-size: 16px;
                    width: 20%;
                }
                .forecast-condition {
                    font-size: 16px;
                    color: #666;
                }
                
            </style>
        </head>
        <body>
            <div class="weather-widget">
                <div class="weather-header">
                    Thời tiết <?php echo esc_html($city_name); ?> hôm nay
                </div>
                <div class="weather-content">
                    <div class="weather-current">
                        <div class="weather-temp"><?php echo esc_html($weather_data['temperature']); ?>°C</div>
                        <div class="weather-condition"><?php echo esc_html($weather_data['condition']); ?></div>
                    </div>
                    <div class="weather-forecast">
                        <?php 
                        $num_days = min(intval($days), count($weather_data['forecast']));
                        for ($i = 0; $i < $num_days; $i++): 
                            $item = $weather_data['forecast'][$i];
                        ?>
                        <div class="forecast-item">
                            <div class="forecast-day">
                                <?php echo esc_html($item['day']); ?><br>
                                <?php echo esc_html($item['date']); ?>
                            </div>
                            <div class="forecast-icon">☀️</div>
                            <div class="forecast-temp">
                                <?php echo esc_html($item['temp_min']); ?>° | <?php echo esc_html($item['temp_max']); ?>°
                            </div>
                            <div class="forecast-condition">
                                <?php echo esc_html($item['condition']); ?>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    private function get_sample_weather_data($city_name) {
        return array(
            'city' => $city_name,
            'temperature' => rand(25, 35),
            'condition' => 'Có bầu trời quang đãng',
            'humidity' => rand(60, 80),
            'wind_speed' => rand(5, 15),
            'icon' => '01d',
            'forecast' => array(
                array('day' => 'T2', 'date' => '25/11', 'temp_min' => 18, 'temp_max' => 25, 'condition' => 'Bầu trời quang đãng', 'icon' => '01d'),
                array('day' => 'T3', 'date' => '26/11', 'temp_min' => 16, 'temp_max' => 24, 'condition' => 'Mây rải rác', 'icon' => '02d'),
                array('day' => 'T4', 'date' => '27/11', 'temp_min' => 16, 'temp_max' => 24, 'condition' => 'Mây cụm', 'icon' => '03d'),
                array('day' => 'T5', 'date' => '28/11', 'temp_min' => 17, 'temp_max' => 25, 'condition' => 'Mây đen u ám', 'icon' => '04d'),
                array('day' => 'T6', 'date' => '29/11', 'temp_min' => 18, 'temp_max' => 26, 'condition' => 'Bầu trời quang đãng', 'icon' => '01d'),
                array('day' => 'T7', 'date' => '30/11', 'temp_min' => 19, 'temp_max' => 27, 'condition' => 'Nắng', 'icon' => '01d'),
                array('day' => 'CN', 'date' => '01/12', 'temp_min' => 20, 'temp_max' => 28, 'condition' => 'Nắng gắt', 'icon' => '01d'),
            )
        );
    }
    
    public function get_weather_data() {
        check_ajax_referer('weather_widget_nonce', 'nonce');
        
        $city_slug = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : 'ha-noi';
        
        // Get city name
        $city_term = get_term_by('slug', $city_slug, 'city');
        $city_name = $city_term ? $city_term->name : 'Hà Nội';
        
        // Get sample data
        $weather_data = $this->get_sample_weather_data($city_name);
        
        wp_send_json_success($weather_data);
    }
}

// Widget Class
class Weather_Widget_Generator_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'weather_widget_generator',
            'Weather Widget Generator',
            array('description' => 'Hiển thị widget tạo mã nhúng thời tiết')
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        $city = !empty($instance['city']) ? $instance['city'] : 'ha-noi';
        $days = !empty($instance['days']) ? $instance['days'] : '3';
        $width = !empty($instance['width']) ? $instance['width'] : '500';
        
        $plugin = new Weather_Widget_Generator();
        echo $plugin->render_widget_generator(array(
            'city' => $city,
            'days' => $days,
            'width' => $width,
            'header_color' => '#ffffff',
            'bg_header_color' => '#16a34a',
            'text_color' => '#000000',
            'border_color' => '#16a34a',
        ));
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $city = !empty($instance['city']) ? $instance['city'] : 'ha-noi';
        $days = !empty($instance['days']) ? $instance['days'] : '3';
        $width = !empty($instance['width']) ? $instance['width'] : '500';
        
        $cities = get_terms(array(
            'taxonomy' => 'city',
            'hide_empty' => false,
        ));
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('city'); ?>">Thành phố:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('city'); ?>" name="<?php echo $this->get_field_name('city'); ?>">
                <?php 
                if (!empty($cities) && !is_wp_error($cities)) {
                    foreach ($cities as $city_term) {
                        $selected = ($city_term->slug == $city) ? 'selected' : '';
                        echo '<option value="' . esc_attr($city_term->slug) . '" ' . $selected . '>' . esc_html($city_term->name) . '</option>';
                    }
                } else {
                    echo '<option value="ha-noi">Hà Nội</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('days'); ?>">Số ngày:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('days'); ?>" name="<?php echo $this->get_field_name('days'); ?>">
                <option value="3" <?php selected($days, '3'); ?>>3 ngày</option>
                <option value="5" <?php selected($days, '5'); ?>>5 ngày</option>
                <option value="7" <?php selected($days, '7'); ?>>7 ngày</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>">Chiều rộng (px):</label>
            <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="number" value="<?php echo esc_attr($width); ?>" min="300" max="800">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['city'] = !empty($new_instance['city']) ? sanitize_text_field($new_instance['city']) : 'ha-noi';
        $instance['days'] = !empty($new_instance['days']) ? sanitize_text_field($new_instance['days']) : '3';
        $instance['width'] = !empty($new_instance['width']) ? intval($new_instance['width']) : 500;
        return $instance;
    }
}

// Initialize plugin
$weather_widget_generator = new Weather_Widget_Generator();

// Activation hook to flush rewrite rules
register_activation_hook(__FILE__, 'weather_widget_activation');
function weather_widget_activation() {
    $plugin = new Weather_Widget_Generator();
    $plugin->add_iframe_rewrite_rules();
    flush_rewrite_rules();
}

// Deactivation hook to flush rewrite rules
register_deactivation_hook(__FILE__, 'weather_widget_deactivation');
function weather_widget_deactivation() {
    flush_rewrite_rules();
}