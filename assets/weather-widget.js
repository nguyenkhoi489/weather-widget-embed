// Weather icons mapping
const weatherIcons = {
    '01d': '☀️', '01n': '🌙',
    '02d': '⛅', '02n': '☁️',
    '03d': '☁️', '03n': '☁️',
    '04d': '☁️', '04n': '☁️',
    '09d': '🌧️', '09n': '🌧️',
    '10d': '🌦️', '10n': '🌧️',
    '11d': '⛈️', '11n': '⛈️',
    '13d': '❄️', '13n': '❄️',
    '50d': '🌫️', '50n': '🌫️'
};

function loadWeatherWidget(element) {
    const city = element.attr('data-city') || 'Ho Chi Minh';
    const showForecast = element.attr('data-forecast') !== 'false';
    
    jQuery.ajax({
        url: weatherWidget.ajax_url,
        type: 'POST',
        data: {
            action: 'get_weather',
            nonce: weatherWidget.nonce,
            city: city
        },
        success: function(response) {
            if (response.success) {
                renderWeatherWidget(element, response.data, showForecast);
            } else {
                element.html('<div class="weather-widget-error">Không thể tải dữ liệu thời tiết</div>');
            }
        },
        error: function() {
            element.html('<div class="weather-widget-error">Lỗi kết nối</div>');
        }
    });
}

function renderWeatherWidget(element, data, showForecast) {
    const now = new Date();
    const dateStr = now.toLocaleDateString('vi-VN', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    let html = `
        <div class="weather-header">
            <div>
                <div class="weather-city">${data.city}</div>
                <div class="weather-date">${dateStr}</div>
            </div>
        </div>
        
        <div class="weather-main">
            <div>
                <div class="weather-temp">${data.temperature}°C</div>
                <div class="weather-condition">${data.condition}</div>
            </div>
            <div class="weather-icon">${weatherIcons[data.icon] || '☀️'}</div>
        </div>
        
        <div class="weather-details">
            <div class="weather-detail-item">
                <span class="weather-detail-icon">💧</span>
                <span>Độ ẩm: ${data.humidity}%</span>
            </div>
            <div class="weather-detail-item">
                <span class="weather-detail-icon">💨</span>
                <span>Gió: ${data.wind_speed} km/h</span>
            </div>
        </div>
    `;
    
    if (showForecast && data.forecast) {
        html += `
            <div class="weather-forecast">
                <div class="forecast-title">Dự báo 5 ngày tới</div>
                <div class="forecast-items">
        `;
        
        data.forecast.forEach(item => {
            html += `
                <div class="forecast-item">
                    <div class="forecast-day">${item.day}</div>
                    <div class="forecast-icon">${weatherIcons[item.icon] || '☀️'}</div>
                    <div class="forecast-temp">${item.temp}°C</div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    element.html(html).addClass('loaded');
}

// Initialize widgets on page load
jQuery(document).ready(function($) {
    $('.weather-widget').each(function() {
        loadWeatherWidget($(this));
    });
});

// For embedded widgets (external websites)
if (typeof window.initWeatherWidgets === 'undefined') {
    window.initWeatherWidgets = function() {
        jQuery('.weather-widget-embed').each(function() {
            const $this = jQuery(this);
            const city = $this.attr('data-city') || 'Ho Chi Minh';
            const theme = $this.attr('data-theme') || 'light';
            const forecast = $this.attr('data-forecast') !== 'false';
            
            $this.addClass('weather-widget')
                .attr('data-theme', theme)
                .html('<div class="weather-widget-loading">Đang tải...</div>');
            
            loadWeatherWidget($this);
        });
    };
}