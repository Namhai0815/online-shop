# Fultala – Online Flower Shop

## Суулгах заавар

### 1. MySQL схем оруулах
MySQL Workbench дээр:
1. `online_shop` датабейзаа нээнэ
2. File → Open SQL Script → `database/schema.sql` сонгоно
3. ⚡ (Execute) товч дарж ажиллуулна

### 2. PHP тохиргоо
`php/config.php` файлыг нээж MySQL нууц үгээ тохируулна:
```php
define('DB_PASS', 'таны_нууц_үг');   // root-ын нууц үг
define('SITE_URL', 'http://localhost/online-shop');
```


## Бүтэц
```
online-shop/
├── index.php          ← Нүүр хуудас
├── database/
│   └── schema.sql     ← MySQL схем
├── php/
│   ├── config.php     ← DB тохиргоо
│   ├── functions.php  ← Helper функцүүд
│   ├── header.php     ← Navbar
│   └── footer.php     ← Footer
├── api/
│   ├── products.php   ← Бүтээгдэхүүний API
│   ├── auth.php       ← Нэвтрэх/бүртгэх API
│   ├── cart.php       ← Сагсны API
│   ├── orders.php     ← Захиалгын API
│   └── wishlist.php   ← Хүслийн жагсаалт API
├── pages/
│   ├── shop.php       ← Дэлгүүр
│   ├── product.php    ← Бүтээгдэхүүний дэлгэрэнгүй
│   ├── cart.php       ← Сагс
│   ├── checkout.php   ← Захиалга
│   ├── login.php      ← Нэвтрэх
│   ├── register.php   ← Бүртгүүлэх
│   ├── orders.php     ← Захиалгын түүх
│   ├── wishlist.php   ← Хүслийн жагсаалт
│   ├── about.php      ← Бидний тухай
│   └── contact.php    ← Холбоо барих
├── css/
│   └── style.css      ← Загвар
└── js/
    └── main.js        ← JavaScript
```
# online-shop
