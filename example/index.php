<?php
die; //В целях безопасности. При использовании удалить.

//Подключаем библиотеку LI AMOCRM
require_once "../src/amocrm_api.php";

//Логин в AMOCRM (емейл)
$email = 'test-admin@admin.email';
//Хэш для доступа к API (смотрите в профиле пользователя)
$api_key = 'c50962868c5c2459b80efbf2efb61d7d';
//Субдомен в AMOCRM
$subdomain = 'new57ea77a2cf8d4';

//Создаем новый экземпляр класса библиотеки
$AMOCRM = new LI_AMOCRM\Api($email, $api_key, $subdomain);

//Телефон
$phone = '80001112233';
//Имя
$name = 'test';
//Email
$email = 'test@test.test';
//budget
$budget = '10000';

//Айди ответственного за заявку в AMOCRM
$responsible_person_id = 12050262;

//Авторизуемся
$aut = $AMOCRM->aut();

//Добавляем контакт, если он не существует.
//$phone - переменная с телефоном по которому происходит поиск.
$req_contact = $AMOCRM->add_contactIfNotExistByPhone($phone, array(
    //Кастомные поля нового контакта
    array(
        'id'=>1063056,
        'values'=>array(
            array(
                'value'=>$phone,
                'enum'=>'MOB'
            )
        )
    ),
    array(
        'id'=>1063058,
        'values'=>array(
            array(
                'value'=>$email,
                'enum'=>'WORK'
            )
        )
    )
),$name);
//Получаем первый контакт с ответа сервера
$contact = $req_contact['contact'];
//Получаем айди первого контакта с ответа сервера
$contact_id = $req_contact['contact_id'];

//Добавляем новый лид
//Первый параметр - название лида
$lead = $AMOCRM->add_lead('Заявка с сайта(сделка)', $responsible_person_id, array(
    //Кастомные поля нового лида
    array(
        'id'=>1074666,
        'values'=>array(
            array(
                'value'=>$budget
            )
        )
    )
));
//Получаем айди добавленного лида
$lead_id = $lead['added_id'];

//Привязываем добавленный лид к контакту
$attach_leadToContact = $AMOCRM->attach_leadToContact($lead_id, $contact);

//Создаем задачу
$add_task = $AMOCRM->add_task($contact_id, 'Заявка с сайта(задача)', $lead_id, $responsible_person_id);


