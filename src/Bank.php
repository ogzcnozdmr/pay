<?php

namespace Oguzcan;

class Bank
{
    private string $name;
    private string $key;
    private int $type;
    private string $apiUrl;
    private string $apiUrl3d;
    private string $storeType;
    private string $storeType3d;
    private \stdClass $settings;
    private \stdClass $security;
    public function __construct(string $bank)
    {
        $data = json_decode(file_get_contents('./Data/bank.json'));
        if (empty($data[$bank])) {
            return 'Banka bilgisi bulunmadı';
        }

        $data = json_decode(file_get_contents('./Data/settings.json'));
        if (empty($data->{$bank})) {
            return 'Ayar bilgisi bulunmadı';
        }
        $this->settings = $data->{$bank};

        $this->key = $bank;
        $this->name = $data[$bank]['name'];
        $this->type = $data[$bank]['type'];
        $this->apiUrl = $data[$bank]['apiUrl'];
        $this->apiUrl3d = $data[$bank]['apiUrl3d'];
        $this->storeType = $data[$bank]['storeType'];
        $this->storeType3d = $data[$bank]['storeType3d'];

        $this->security = new \stdClass();
        $this->security->name = '';
        $this->security->password = '';
        $this->security->client = '';
        $this->security->storeKey = '';
    }

    /**
     * Set Security Name
     * @param string $value
     * @return void
     */
    public function setSecurityName(string $value) : void
    {
        $this->security->name = $value;
    }

    /**
     * Set Security Password
     * @param string $value
     * @return void
     */
    public function setSecurityPassword(string $value): void
    {
        $this->security->password = $value;
    }

    /**
     * Set Security Client
     * @param string $value
     * @return void
     */
    public function setSecurityClient(string $value): void
    {
        $this->security->client = $value;
    }

    /**
     * Set Security Store Key
     * @param string $value
     * @return void
     */
    public function setSecurityStoreKey(string $value): void
    {
        $this->security->storeKey = $value;
    }

    /**
     * Set Settings
     * @param string $key
     * @param string|int $value
     * @return void
     */
    public function setSettings(string $key, string|int $value): void
    {
        if (isset($this->settings->{$key})) {
            $this->settings->{$key} = $value;
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
     * @return array
     */
    public function getSettings() : array
    {
        return $this->settings;
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
        return $this->security->name;
    }

    /**
     * Get Security Password
     * @return string
     */
    public function getSecurityPassword() : string
    {
        return $this->security->password;
    }

    /**
     * Get Security Client
     * @return string
     */
    public function getSecurityClient() : string
    {
        return $this->security->client;
    }

    /**
     * Get Security Store Key
     * @return string
     */
    public function getSecurityStoreKey() : string
    {
        return $this->security->storeKey;
    }
}