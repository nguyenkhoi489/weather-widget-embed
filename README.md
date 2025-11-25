# Weather Widget Generator

**Plugin tạo widget thời tiết có thể nhúng vào WordPress với khả năng tùy chỉnh giao diện và tạo mã embed.**

## 📋 Mô tả

Weather Widget Generator là một plugin WordPress cho phép bạn tạo và tùy chỉnh widget hiển thị thông tin thời tiết với khả năng:
- Tùy chỉnh giao diện (màu sắc, kích thước)
- Chọn địa điểm và số ngày dự báo
- Tạo mã nhúng (iframe) để sử dụng trên website khác
- Hỗ trợ shortcode và widget sidebar
- Responsive trên mọi thiết bị

## ✨ Tính năng

- ✅ **Widget có thể kéo thả**: Dễ dàng thêm vào sidebar hoặc page builder
- ✅ **Shortcode linh hoạt**: Nhúng widget ở bất kỳ đâu với nhiều tùy chọn
- ✅ **Tùy chỉnh màu sắc**: Màu tiêu đề, nền, văn bản, viền
- ✅ **Responsive Design**: Tự động điều chỉnh theo kích thước màn hình
- ✅ **Dự báo đa ngày**: 3, 5 hoặc 7 ngày
- ✅ **Mã nhúng iframe**: Chia sẻ widget sang website khác
- ✅ **AJAX loading**: Tải dữ liệu không cần refresh trang

## 🚀 Cài đặt

### Cách 1: Upload qua WordPress Admin

1. Tải xuống file plugin (zip)
2. Đăng nhập vào WordPress Admin
3. Vào **Plugins** → **Add New** → **Upload Plugin**
4. Chọn file zip và nhấn **Install Now**
5. Nhấn **Activate** để kích hoạt plugin

### Cách 2: Upload qua FTP

1. Tải và giải nén file plugin
2. Upload thư mục `weather-widget-embed` vào `/wp-content/plugins/`
3. Vào **Plugins** trong WordPress Admin
4. Tìm "Weather Widget Generator" và nhấn **Activate**

## 📖 Hướng dẫn sử dụng

### 1. Sử dụng trong Sidebar Widget

1. Vào **Appearance** → **Widgets**
2. Tìm widget **"Weather Widget Generator"**
3. Kéo thả vào sidebar mong muốn
4. Cấu hình:
   - Chọn thành phố
   - Số ngày dự báo (3, 5, 7)
   - Chiều rộng widget
5. Nhấn **Save**

### 2. Sử dụng Shortcode

Thêm shortcode vào bài viết hoặc trang:

```php
[weather_widget_generator]
```

#### Shortcode với tham số:

```php
[weather_widget_generator city="ha-noi" days="5" width="600" bg_header_color="#0066cc"]
```

#### Các tham số khả dụng:

| Tham số | Mô tả | Giá trị mặc định | Ví dụ |
|---------|-------|------------------|-------|
| `city` | Slug của thành phố | `ha-noi` | `ha-noi`, `ho-chi-minh` |
| `days` | Số ngày dự báo | `3` | `3`, `5`, `7` |
| `width` | Chiều rộng widget (px) | `500` | `300` - `800` |
| `header_color` | Màu chữ tiêu đề | `#ffffff` | `#000000`, `#ffffff` |
| `bg_header_color` | Màu nền tiêu đề | `#16a34a` | `#0066cc`, `#ff5733` |
| `text_color` | Màu văn bản nội dung | `#000000` | `#333333`, `#666666` |
| `border_color` | Màu viền widget | `#16a34a` | `#cccccc`, `#0066cc` |

### 3. Tạo mã nhúng (Embed Code)

1. Vào **Weather Widget** trong menu WordPress Admin
2. Hoặc thêm shortcode vào trang để hiển thị giao diện tùy chỉnh
3. Điều chỉnh các thông số:
   - Chọn địa điểm
   - Kích thước widget
   - Số ngày dự báo
   - Màu sắc
4. Nhấn **"🔄 Cập nhật Preview"** để xem kết quả
5. Copy mã nhúng từ textarea
6. Dán mã vào website khác

#### Ví dụ mã nhúng:

```html
<iframe src="https://yourdomain.com/iframe/?city=ha-noi&days=3&header_color=%23ffffff&bg_header_color=%2316a34a&text_color=%23000000&border_color=%2316a34a" width="100%" height="400" frameborder="0" style="border:none;"></iframe>
```

### 4. Sử dụng trong Page Builder

Plugin tương thích với các page builder phổ biến như:
- Elementor
- WPBakery
- Beaver Builder
- Divi Builder

**Cách sử dụng:**
1. Tìm widget HTML/Shortcode trong page builder
2. Thêm shortcode `[weather_widget_generator]` với các tham số
3. Preview và publish

## 🎨 Tùy chỉnh giao diện

### Ví dụ các style khác nhau:

**Style 1: Xanh dương chuyên nghiệp**
```php
[weather_widget_generator bg_header_color="#0066cc" border_color="#0066cc" width="600"]
```

**Style 2: Cam năng động**
```php
[weather_widget_generator bg_header_color="#ff5733" border_color="#ff5733" header_color="#ffffff"]
```

**Style 3: Tím hiện đại**
```php
[weather_widget_generator bg_header_color="#667eea" border_color="#764ba2" width="700"]
```

## 🔧 Yêu cầu hệ thống

- WordPress 5.0 trở lên
- PHP 7.2 trở lên
- Taxonomy "city" đã được tạo (cho danh sách thành phố)

## 📂 Cấu trúc thư mục

```
weather-widget-embed/
│
├── weather-widget-embed.php    # File plugin chính
├── README.md                    # File hướng dẫn này
│
└── assets/                      # Thư mục tài nguyên
    ├── weather-widget.js        # JavaScript xử lý widget
    └── admin-style.css          # CSS cho admin panel
```

## 🔌 API & Hooks

### AJAX Actions

**Lấy dữ liệu thời tiết:**
```javascript
jQuery.ajax({
    url: weatherWidget.ajax_url,
    type: 'POST',
    data: {
        action: 'get_weather',
        nonce: weatherWidget.nonce,
        city: 'ha-noi'
    }
});
```

### Rewrite Rules

Plugin tự động tạo endpoint `/iframe/` để hiển thị widget trong iframe:
```
https://yourdomain.com/iframe/?city=ha-noi&days=3
```

## 🐛 Xử lý sự cố

### Widget không hiển thị?
1. Kiểm tra plugin đã được kích hoạt
2. Xóa cache của website
3. Vào **Settings** → **Permalinks** và nhấn **Save Changes** để flush rewrite rules

### Màu sắc không thay đổi?
1. Nhấn nút "🔄 Cập nhật Preview" sau khi điều chỉnh màu
2. Xóa cache trình duyệt

### Mã nhúng không hoạt động?
1. Kiểm tra URL trong iframe có đúng không
2. Đảm bảo website cho phép nhúng iframe
3. Kiểm tra rewrite rules đã được flush

## 📝 Changelog

### Version 1.0.0
- ✨ Phát hành phiên bản đầu tiên
- ✅ Widget sidebar
- ✅ Shortcode support
- ✅ Iframe embed generator
- ✅ Color customization
- ✅ Multi-day forecast (3/5/7 days)
- ✅ Responsive design
- ✅ AJAX weather loading

## 👨‍💻 Tác giả

**Nguyên Khôi**
- Website: [https://nguyenkhoi.dev/](https://nguyenkhoi.dev/)

## 📄 License

Plugin này được phát hành dưới giấy phép GPL v2 hoặc mới hơn.

## 🤝 Đóng góp

Mọi đóng góp đều được hoan nghênh! Nếu bạn tìm thấy lỗi hoặc có ý tưởng cải tiến, vui lòng:
1. Tạo issue
2. Fork repository
3. Tạo pull request

## 📞 Hỗ trợ

Nếu bạn cần hỗ trợ hoặc có câu hỏi:
- Email: support@nguyenkhoi.dev
- Website: https://nguyenkhoi.dev/

---

**Cảm ơn bạn đã sử dụng Weather Widget Generator! ⛅**
