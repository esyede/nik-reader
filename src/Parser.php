<?php

namespace ZerosDev\NikReader;

class Parser
{
    private $nik;

    private static $database;
    private static $filemtime;


    public function __construct(string $nik, string $database = null)
    {
        $this->setNik($nik);

        $database = $database ?? dirname(__DIR__) . '/database/wilayah.json';

        $this->setDatabase($database);
    }


    public function isValid()
    {
        return is_string($this->nik) && strlen($this->nik) === 16;
    }


    public function setNik(string $nik)
    {
        if (! $this->isValid($nik)) {
            throw new Exceptions\InvalidNikNumberException(sprintf(
                'NIK number should be a 16-digit numeric string. Got: %s',
                gettype($nik)
            ));
        }

        $this->nik = $nik;

        return $this;
    }


    public function setDatabase($file)
    {
        if (! is_file($file) || ! is_readable($file)) {
            throw new Exceptions\InvalidDatabaseWilayahException(sprintf(
                'The database file cannot be found or not readable: %s',
                $file
            ));
        }

        if (static::$filemtime <= filemtime($file)) {
            static::$database = file_get_contents($file);
            static::$filemtime = filemtime($file);
        }

        return $this;
    }


    public function getProvinsi()
    {
        $code = substr($this->nik, 0, 2);

        return static::$database->provinsi->{$code} ?? null;
    }


    public function getKabupatenKota()
    {
        $code = substr($this->nik, 0, 4);

        return static::$database->kabkot->{$code} ?? null;
    }


    public function getKecamatan()
    {
        $code = substr($this->nik, 0, 6);

        return static::$database->kecamatan->{$code} ?? null;
    }


    public function getTanggalLahir()
    {
        $code = substr($this->nik, 6, 12);
        list($day, $month, $year) = str_split($code, 2);

        try {
            return DateTime::createFromFormat(
                'd-m-Y',
                sprintf('%s-%s-%s', $day, $month, $year)
            )->format('d-m-Y');
        } catch (\Exception $e) {
            throw new Exceptions\InvalidDateOfBirthException(sprintf(
                'Unable to parse date of birth (%s) from an invalid NIK number (%s)',
                $code, $this->nik
            ));
        }
    }
}
