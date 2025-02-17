<?php

namespace Oguzcan;

class Bank
{
    private string $name = '';
    private string $key = '';
    private int $type = 1;
    private string $apiUrl = '';
    private string $apiUrl3d = '';
    private string $storeType = '';
    private string $storeType3d = '';
    private array $settings = [];
    private array $security = [
        'name' => '',
        'password' => '',
        'client' => '',
        'storeKey' => ''
    ];
    public function __construct(string $bank)
    {
        $data = __pay_json_decode(file_get_contents(__DIR__ . '/Data/bank.json'), true);
        if (empty($data[$bank])) {
            return 'Banka bilgisi bulunmadı';
        }

        $settings = __pay_json_decode(file_get_contents(__DIR__ . '/Data/settings.json'), true);
        if (empty($settings[$bank])) {
            return 'Ayar bilgisi bulunmadı';
        }
        $this->settings = $settings[$bank];

        echo "data bank";
        print_r($data[$bank]);

        $this->key = $bank;
        $this->name = $data[$bank]['name'];
        $this->type = $data[$bank]['type'];
        $this->apiUrl = $data[$bank]['apiUrl'];
        $this->apiUrl3d = $data[$bank]['apiUrl3d'];
        $this->storeType = $data[$bank]['storeType'];
        $this->storeType3d = $data[$bank]['storeType3d'];
    }

    /**
     * Set Security Name
     * @param string $value
     * @return void
     */
    public function setSecurityName(string $value) : void
    {
        $this->security['name'] = $value;
    }

    /**
     * Set Security Password
     * @param string $value
     * @return void
     */
    public function setSecurityPassword(string $value): void
    {
        $this->security['password'] = $value;
    }

    /**
     * Set Security Client
     * @param string $value
     * @return void
     */
    public function setSecurityClient(string $value): void
    {
        $this->security['client'] = $value;
    }

    /**
     * Set Security Store Key
     * @param string $value
     * @return void
     */
    public function setSecurityStoreKey(string $value): void
    {
        $this->security['storeKey'] = $value;
    }

    /**
     * Set Settings
     * @param string $key
     * @param string|int $value
     * @return void
     */
    public function setSettings(string $key, string|int $value): void
    {
        if (isset($this->settings[$key])) {
            $this->settings[$key] = $value;
        }
    }

    /**
     * Get Name
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get Key
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * Get Type
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }

    /**
     * Get Api Url
     * @return string
     */
    public function getApiUrl() : string
    {
        return $this->apiUrl;
    }

    /**
     * Get Api Url 3d
     * @return string
     */
    public function getApiUrl3d() : string
    {
        return $this->apiUrl3d;
    }

    /**
     * Get Api Store Type
     * @return string
     */
    public function getStoreType() : string
    {
        return $this->storeType;
    }

    /**
     * Get Settings
     * @return mixed
     */
    public function getSettings(string $key) : mixed
    {
        return $this->settings[$key] ?? '';
    }

    /**
     * Get Api Store Type 3d
     * @return string
     */
    public function getStoreType3d() : string
    {
        return $this->storeType3d;
    }

    /**
     * Get Security Name
     * @return string
     */
    public function getSecurityName() : string
    {
        return $this->security['name'];
    }

    /**
     * Get Security Password
     * @return string
     */
    public function getSecurityPassword() : string
    {
        return $this->security['password'];
    }

    /**
     * Get Security Client
     * @return string
     */
    public function getSecurityClient() : string
    {
        return $this->security['client'];
    }

    /**
     * Get Security Store Key
     * @return string
     */
    public function getSecurityStoreKey() : string
    {
        return $this->security['storeKey'];
    }
}