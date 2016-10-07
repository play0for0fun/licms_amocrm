# The LI AMOCRM (https://www.amocrm.ru/) Api client v1.1 for PHP.

(c) Land Iguana <info@landiguana.com>
For the full copyright and license information, please view the LICENSE file that was distributed with this source code.

## Getting Started

Подключение библиотеки LI AMOCRM :

```php
require_once "../src/amocrm_api.php";
```

Создание нового экземпляра класса библиотеки :

```php
$AMOCRM = new LI_AMOCRM\Api($email, $api_key, $subdomain);
```

Авторизация :

```php
$aut = $AMOCRM->aut();
```

### Usage

#### Детальный пример использования находится в файле example.php, который распространяется вместе с библиотекой.

Добавление контакта, если он не существует.

```php
$req_contact = $AMOCRM->add_contactIfNotExistByPhone($phone, array(
    //Кастомные поля нового контакта
));
//Получаем первый контакт с ответа сервера
$contact = $req_contact['contact'];
//Получаем айди первого контакта с ответа сервера
$contact_id = $req_contact['contact_id'];
```

Добавление нового лида

```php
$lead = $AMOCRM->add_lead('Request from site SITE_NAME', $responsible_person_id, array(
    //Кастомные поля нового лида
));
//Получаем айди добавленного лида
$lead_id = $lead['added_id'];
```

Привязка лида к контакту

```php
$attach_leadToContact = $AMOCRM->attach_leadToContact($lead_id, $contact);
```

Создание задачи

```php
$add_task = $AMOCRM->add_task($contact_id, 'Request from site SITE_NAME', $responsible_person_id);
```

### Additionally

Логи с ошибками записываются в файл errors.log который автоматически создается в директории с файлом библиотеки. (Даты UTC)

В файл amocrm_cookie.data автоматически записываются куки для авторизации в системе.

.htaccess в директории библиотеки предназначен для блокировки доступа к файлам необходимым для работы. В целях безопасности его не рекомендуется трогать.