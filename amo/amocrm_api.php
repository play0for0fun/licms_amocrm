<?php
/*
 * This file is part of the `Land Iguana Core` package.
 *
 * (c) Land Iguana <info@landiguana.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Land Iguana https://landiguana.com/
 */

namespace LI_AMOCRM;

/**
 * The LI AMOCRM (https://www.amocrm.ru/) Api client for PHP.
 *
 * v 1.1.1
 *
 * @method void error() error(string $text) Put text to the log
 * @method array request() request(string $link, string $type, array $data,  array $_params = array()) Universal requested curl
 * @method array aut() aut() Authorization
 * @method array get_contactByPhone() get_contactByPhone(string $phone) Check contact by phone
 * @method array add_contact() add_contact(string $name, array $fields) Adding contact
 * @method array add_contactIfNotExistByPhone() add_contactIfNotExistByPhone(string $phone, array $custom_fileds, string $name = '') Adding a contact if it does not exist.
 * @method array add_lead() add_lead(string $name, int $responsible_person_id, array $custom = array()) Adding a new lead
 * @method array attach_leadToContact() attach_leadToContact(int $lead_id, array $contact) Linking lead to contact
 * @method array add_task() add_task(int $contact_id, string $text, int $responsible_user_id) Creating a task
 *
 * @author Land Iguana <info@landiguana.com>
 */

date_default_timezone_set("UTC");

define('TIME_UTC', time());

class Api {

    /**
     * The directory of main class file.
     *
     * @var string
     */
    public $dir;

    /**
     * The cookie data file.
     *
     * const string
     */
    const cookie_file = 'amocrm_cookie.data';

    /**
     * The cookie data file.
     *
     * const string
     */
    const error_log_file = 'errors.log';

    /**
     * The cookie data file.
     *
     * @var string
     */
    protected $cookie_path;

    /**
     * The user login in AMOCRM system.
     *
     * @var string
     */
    protected $login;

    /**
     * The user access token in AMOCRM api system.
     *
     * @var string
     */
    protected $key;

    /**
     * The user subdomain in AMOCRM system.
     *
     * @var string
     */
    protected $subdomain;

    /**
     * The user subdomain in AMOCRM system.
     *
     * @var array
     */
    private $urls;

    /**
     * Constructor.
     *
     * @param  resource|null $connection
     */
    public function __construct($login, $key, $subdomain) {

        $this->dir = dirname(__FILE__);

        $this->cookie_path = $this->dir . '/' . $this::cookie_file;

        $this->login = $login;

        $this->key = $key;

        $this->subdomain = $subdomain;

        if (empty($login) or empty($key) or empty($subdomain)) {
            $this->error('Wrong aut data');
        }

        $this->urls = array(
            'aut' => 'https://' . $this->subdomain . '.amocrm.ru/private/api/auth.php?type=json',
            'get_contact' => 'https://' . $this->subdomain . '.amocrm.ru/private/api/v2/json/contacts/list',
            'add_lead' => 'https://' .  $this->subdomain . '.amocrm.ru/private/api/v2/json/leads/set',
            'add_contact' => 'https://' .  $this->subdomain . '.amocrm.ru/private/api/v2/json/contacts/set',
            'add_task' => 'https://' .  $this->subdomain . '.amocrm.ru/private/api/v2/json/tasks/set',
        );


    }

    public function error($text) {

        $date = date('Y-m-d H:i:s', TIME_UTC);

        $error = '[' . $date . '] ' . $text . "\n";

        $data = '';

        if (file_exists($this->dir . '/' . $this::error_log_file)) {

            $data = file_get_contents($this->dir . '/' . $this::error_log_file);

        }

        @file_put_contents($this->dir . '/' . $this::error_log_file, $error . $data);

    }

    public function request ($link, $type, $data, $_params = array()) {

        $params = array(
            'method' => $_params['method'] || 'post',
            //'uagent' =>
        );

        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL

        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);

        if (!empty($_params['uagent'])) {
            curl_setopt($curl,CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        }

        if ($type === 'get') {
            $link .= '?' . $data;
        }

        curl_setopt($curl, CURLOPT_URL, $link);

        if ($type === 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        } else if ($type === 'postJSON') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        }


        curl_setopt($curl, CURLOPT_HEADER, false);

        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_path);

        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_path);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        $out = curl_exec($curl);

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $return = array();

        $return['json'] = $out;

        $out = json_decode($out, true);

        $return['res'] = $code == '200' ? true:false;
        $return['code'] = $code;
        $return['data'] = $out;

        if (empty($out)) {
            $this->error('Empty server response');
        }
        if (!empty($return['data']['response']['error_code'])) {
            $this->error('Error request url: [' . $link . '], error code: ' . $return['data']['response']['error_code'] . ', error: [' . $return['data']['response']['error'] . ']');
        }

        return $return;

    }

    public function aut () {

        $r = $this->request($this->urls['aut'], 'post', http_build_query(array(
            'USER_LOGIN' => $this->login, #Ваш логин (электронная почта)
            'USER_HASH' => $this->key #Хэш для доступа к API (смотрите в профиле пользователя)
        )));


        if ($r['data']['response']['error_code'] == '110') {
            $this->error('Aut error. :( Check aut data.');
        }

        return $r;

    }

    public function get_contactByPhone ($phone) {

        $r = $this->request($this->urls['get_contact'], 'get', http_build_query(array(
            'query' => $phone
        )));

        $r['contact'] = $r['data']['response']['contacts'][0];
        
        $r['contact_id'] = $r['contact']['id']/*[0]*/;

        return $r;

    }

    public function add_contact ($name, $fields) {

        $date = date("d.m.Y");


        $d['request']['contacts']['add'] = array(
            array(
                'name'=>$name,
//                'linked_leads_id'=>array($lead_id),
                'custom_fields' => $fields
            )
        );
        //print_r(json_encode($d, true));

        $request = $this->request($this->urls['add_contact'], 'postJSON', json_encode($d));



        $request['contact'] = $request['data']['response']['contacts']['add'][0];

        $request['contact_id'] =  $request['data']['response']['contacts']['add'][0]['id'];

        return $request;

    }

    public function add_contactIfNotExistByPhone ($phone, $custom_fileds, $name = '') {

        $req_contact = $this->get_contactByPhone($phone);
        $contact = $req_contact['contact'];

        if (empty($contact)) {

            if (empty($name)) {
                $name = $phone;
            }

            $req_contact = $this->add_contact($name, $custom_fileds);

            $req_contact = $this->get_contactByPhone($phone);

        }



        return $req_contact;

    }

    public function add_lead ($name, $responsible_person_id, $custom = array()) {

        $request = $this->request($this->urls['add_lead'], 'postJSON', json_encode(
                array(
                    'request' => array(
                        'leads' => array(
                            'add' => array(
                                array(
                                    'name' => $name,
                                    'status_id' => 140,
                                    'responsible_user_id' => $responsible_person_id,
                                    'custom_fields' => $custom
                                )
                            )
                        )
                    )
                ))
        );

        if ($request['res'] == true) {

            if (!empty($request['data'])) {

                $request['added_id'] = $request['data']['response']['leads']['add'][0]['id'];

            } else {

                $this->error('Empty request data (line ' . __LINE__);

            }

        }

        return $request;

    }

    public function attach_leadToContact ($lead_id, $contact) {

        array_push($contact['linked_leads_id'], $lead_id);

        $request = $this->request($this->urls['add_contact'], 'postJSON', json_encode(
                array(
                    'request' => array(
                        'contacts' => array(
                            'update' => array(
                                $contact
                            )
                        )
                    )
                ))
        );

        return $request;

    }

    public function add_task ($contact_id, $text, $lead_id,$responsible_user_id) {

        $request = $this->request($this->urls['add_task'], 'postJSON', json_encode(
                array(
                    'request' => array(
                        'tasks' => array(
                            'add' => array(
                                array(
                                    'element_id'=>$lead_id,
                                    'element_type'=>2,
                                    'task_type'=>3,
                                    'text'=>$text,
                                    'responsible_user_id'=>$responsible_user_id,
                                    'complete_till'=>time()
                                )
                            )
                        )
                    )
                ))
        );

        return $request;

    }


}

