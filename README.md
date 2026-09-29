# Iran SOCKS5 Exit Proxy

خروجی IP ایران برای بک‌اندهایی که بیرون از ایران هستن

[![Author](https://img.shields.io/badge/Author-Farshad%20Ghanbari-111827?style=for-the-badge)](https://github.com/FarshadGhanbari)
[![Stack](https://img.shields.io/badge/PHP%20%7C%20Laravel%20%7C%20Python%20%7C%20ASP.NET%20%7C%20Node-2563EB?style=for-the-badge)](#نمونه-کد)
[![License](https://img.shields.io/badge/License-MIT-22C55E?style=for-the-badge)](./LICENSE)

خیلی وقتا سرور اپ بیرون ایرانه، ولی بعضی سرویس‌ها یا APIهای داخلی فقط IP ایران رو قبول می‌کنن.

کار ساده است: یه SOCKS5 کوچیک روی VPS ایران بالا بیار، بعد تو کدت (PHP، Laravel، Python، ASP.NET، Node یا هر چیز دیگه) فقط همون درخواستایی که لازم داری رو از پروکسی رد کن.

---

## کی به دردت می‌خوره؟

| آره | نه خیلی |
|-----|---------|
| بک‌اندت بیرون ایرانه (هر زبانی) | می‌خوای کل ترافیک سیستم از پروکسی بره |
| فقط چند تا HTTP call باید با IP ایران برن | دنبال کلاینت دسکتاپ هستی، نه اپ سروری |
| می‌خوای تیم یه روش ثابت داشته باشه | سرور ایران نداری و نمی‌خوای بگیری |

---

## چطور کار می‌کنه؟

```text
┌──────────────────┐     HTTP via SOCKS5      ┌──────────────────┐     IP ایران      ┌──────────────────┐
│  سرور خارج شما    │ ───────────────────────► │  VPS ارزان ایران  │ ───────────────► │  سرویس / API هدف  │
│ PHP / Laravel /  │   socks5h://user:pass@…  │  Dante SOCKS5    │                   │                  │
│ Python / ASP.NET │                          │                  │                   │                  │
│ Node / …         │                          │                  │                   │                  │
└──────────────────┘                          └──────────────────┘                   └──────────────────┘
```

1. روی ایران یه پروکسی با یوزر/پسورد بالا میاری
2. روی سرور خارج، مشخصاتش رو تو env می‌ذاری
3. تو کد فقط درخواستای لازم رو از SOCKS5 رد می‌کنی

---

## فهرست

- [مرحله ۱ — سرور ایران](#مرحله-۱--سرور-ایران)
- [مرحله ۲ — نصب SOCKS5](#مرحله-۲--نصب-socks5-روی-سرور-ایران)
- [مرحله ۳ — وصل کردن بک‌اند](#مرحله-۳--وصل-کردن-بکاند)
- [نمونه کد](#نمونه-کد)
- [تست](#تست)
- [اگه گیر کردی](#اگه-گیر-کردی)
- [چک‌لیست](#چکلیست)

---

## مرحله ۱ — سرور ایران

یه VPS ارزون داخل ایران بگیر.

| مورد | پیشنهاد |
|------|---------|
| OS | Ubuntu 20.04 / 22.04 / 24.04 |
| RAM | ۱ گیگ کافیه |
| دسترسی | SSH با `root` یا sudo |

```bash
ssh root@IP_SERVER_IRAN
```

---

## مرحله ۲ — نصب SOCKS5 روی سرور ایران

```bash
wget https://raw.githubusercontent.com/saaiful/socks5/main/socks5.sh
sudo bash socks5.sh
```

اسکریپت می‌پرسه:

1. **پورت** (پیش‌فرض `1080` — اگه بسته بود بذار `2080` یا `8388`)
2. **یوزرنیم**
3. **پسورد قوی**

آخرش چیزی شبیه این می‌گیری:

```text
socks5://USERNAME:PASSWORD@IP_IRAN:PORT
```

### تست از بیرون

```bash
curl -x socks5h://USERNAME:PASSWORD@IP_IRAN:PORT https://ipinfo.io/
```

اگه `country` شد `IR`، اوکیه.

> بهتره همیشه `socks5h` بزنی تا DNS هم از مسیر ایران حل بشه.

### امنیت

- پسورد ضعیف نذار
- مشخصات پروکسی رو تو کد commit نکن
- اگه می‌تونی تو فایروال فقط IP سرور خارج رو به این پورت باز کن
- بدون auth ولش نکن روی اینترنت

---

## مرحله ۳ — وصل کردن بک‌اند

روی سرور خارج اینا رو تو env بذار (اسم متغیرها مهم نیست، مقدارشون مهمه):

```env
IRAN_SOCKS5_HOST=185.x.x.x
IRAN_SOCKS5_PORT=1080
IRAN_SOCKS5_USER=proxyuser
IRAN_SOCKS5_PASS=change-me-strong-password
```

آدرس کامل:

```text
socks5h://USER:PASS@HOST:PORT
```

همین آدرس رو به HTTP client زبانت بده. نمونه‌ها تو [`examples/`](./examples) هستن.

---

## نمونه کد

### cURL (هر جایی)

```bash
curl -x socks5h://USER:PASS@HOST:PORT https://ipinfo.io/
```

[`examples/curl/request.sh`](./examples/curl/request.sh)

### PHP خالص

```php
$proxy = getenv('IRAN_SOCKS5_PROXY'); // socks5h://user:pass@host:port

$ch = curl_init('https://ipinfo.io/json');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_PROXY => $proxy,
    CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5_HOSTNAME,
    CURLOPT_TIMEOUT => 30,
]);
$body = curl_exec($ch);
curl_close($ch);

echo $body;
```

[`examples/php/iran_socks5.php`](./examples/php/iran_socks5.php)

### Laravel

```php
use App\Support\IranSocks5;

IranSocks5::http()->get('https://ipinfo.io/json')->json();
```

[`examples/laravel/`](./examples/laravel)

### Python

```python
import os
import requests

proxy = os.environ["IRAN_SOCKS5_PROXY"]  # socks5h://user:pass@host:port
proxies = {"http": proxy, "https": proxy}

r = requests.get("https://ipinfo.io/json", proxies=proxies, timeout=30)
print(r.json())
```

اول اینو بزن: `pip install "requests[socks]"`

[`examples/python/iran_socks5.py`](./examples/python/iran_socks5.py)

### ASP.NET (C#)

```csharp
// NuGet: MihaZupan.HttpToSocks5Proxy
using MihaZupan;
using System.Net.Http;

var proxy = new HttpToSocks5Proxy("HOST", PORT, "USER", "PASS");
var handler = new HttpClientHandler { Proxy = proxy };
using var client = new HttpClient(handler, disposeHandler: true);

var json = await client.GetStringAsync("https://ipinfo.io/json");
Console.WriteLine(json);
```

[`examples/aspnet/IranSocks5.cs`](./examples/aspnet/IranSocks5.cs)

### Node.js

```js
import { SocksProxyAgent } from "socks-proxy-agent";

const agent = new SocksProxyAgent(process.env.IRAN_SOCKS5_PROXY);
const res = await fetch("https://ipinfo.io/json", { agent });
console.log(await res.json());
```

اول اینو بزن: `npm i socks-proxy-agent`

[`examples/nodejs/iran_socks5.mjs`](./examples/nodejs/iran_socks5.mjs)

---

## ساختار ریپو

```text
iran-socks5/
├── README.md
├── LICENSE
├── .env.example
└── examples/
    ├── curl/
    ├── php/
    ├── laravel/
    ├── python/
    ├── aspnet/
    └── nodejs/
```

---

## تست

از هر زبانی که هستی یه GET به `https://ipinfo.io/json` بزن.

باید `country = IR` بیاد.

---

## اگه گیر کردی

| مشکل | چیکار کنی |
|------|-----------|
| Timeout | پورت رو تو فایروال باز کن یا پورت دیگه بذار |
| Authentication failed | یوزر رو دوباره با اسکریپت بساز |
| دامنه resolve نمی‌شه | `socks5h://` بزن، نه `socks5://` |
| سرویس بالا نیست | `sudo systemctl status danted` و لاگ `/var/log/danted.log` |

```bash
sudo systemctl status danted
sudo systemctl restart danted
sudo tail -f /var/log/danted.log
```

---

## چک‌لیست

- [ ] VPS ایران گرفتی و SSH وصل شد
- [ ] `socks5.sh` زدی و یوزر ساختی
- [ ] تست `curl` از بیرون IP ایران نشون داد
- [ ] env روی سرور خارج ست شد
- [ ] نمونه مربوط به زبان پروژه‌ت تست شد
- [ ] پسورد و مشخصات پروکسی تو git نیست

---

نصب SOCKS5 روی سرور ایران با اسکریپت [saaiful/socks5](https://github.com/saaiful/socks5) انجام می‌شه. نمونه‌های چندزبانه و ساختار این ریپو اینجان.

MIT
