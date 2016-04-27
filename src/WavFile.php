<?php

namespace ProfIT\Bbb;

/**
 * Class WavFile
 * @package ProfIT\Bbb
 */
class WavFile
{
    /**
     * Описательная часть WAV файла
     * HEADER - основная информация (тип, размер файла)
     * FORMAT - описание звуковых характеристик (частота, битрейт, и т.п.)
     * LIST - дополнительная информация
     * DATA - сами данные
     */
    protected $headerFormat = [
        'headerGroupId'       => 'A4',
        'headerFileSize'      => 'V',
        'headerRiffType'      => 'A4',
        'formatGroupId'       => 'A4',
        'formatChunkSize'     => 'V',
        'formatTag'           => 'v',
        'formatChannels'      => 'v',
        'formatSampleRate'    => 'V',
        'formatByteRate'      => 'V',
        'formatBlockAlign'    => 'v',
        'formatBitsPerSample' => 'v',
        'listGroupId'         => 'A4',
        'listChunkSize'       => 'V',
        'listType'            => 'A4',
        'listData'            => 'A88',
        'dataGroupId'         => 'A4',
        'dataChunkSize'       => 'V',
    ];
    
    const HEADER_OFFSET = 8;
    const DATA_OFFSET = 144;

    protected $fileName;
    public $headers;

    /**
     * @param string $src - полный путь до файла в формате wav
     */
    public function __construct(string $src)
    {
        $this->fileName = $src;
        $this->headers = $this->getHeaders();
    }

    /**
     * @param string $headersPacked - заголовки в сыром виде
     *
     * @return array - заголовки в виде ассоциативного массива
     */
    protected function unpackHeaders($headersPacked)
    {
        $unpackFormat = [];
        foreach ($this->headerFormat as $k => $v) {
            $unpackFormat[] = $v . $k;
        }
        return unpack(implode('/', $unpackFormat), $headersPacked);
    }

    /**
     * @param string $headers - заголовки в виде ассоциативного массива
     *
     * @return string - заголовки в сыром виде
     */
    protected function packHeaders($headers)
    {
        $packFormat = implode('', $this->headerFormat);
        return call_user_func_array('pack', array_merge([$packFormat], $headers));
    }

    /**
     * @return string - заголовки в сыром виде
     *
     * @throws \ProfIT\Bbb\Exception
     */
    protected function getHeadersPacked()
    {
        $src = fopen($this->fileName, 'r');
        if (false === $src) {
            throw new \ProfIT\Bbb\Exception ('Ошибка открытия файла: ' . $this->fileName);
        }

        $headersPacked = fread($src, self::DATA_OFFSET);
        fclose($src);

        return $headersPacked;
    }

    /**
     * @return array - заголовки в виде ассоциативного массива
     */
    protected function getHeaders()
    {
        return $this->unpackHeaders($this->getHeadersPacked());
    }

    /**
     * @param string $dstFileName - путь к файлу, в который нужно записать заголовки
     */
    public function exportHeaders($dstFileName)
    {
        $mode = file_exists($dstFileName) ? 'r+' : 'w';
        $dst = fopen($dstFileName, $mode);

        fwrite($dst, $this->packHeaders($this->headers));

        fclose($dst);
    }

    /**
     * @param string $dstFileName - путь к файлу, в который нужно записать данные
     *
     * @throws \ProfIT\Bbb\Exception
     */
    public function exportData($dstFileName)
    {
        $src = fopen($this->fileName, 'r');
        if (false === $src) {
            throw new \ProfIT\Bbb\Exception ('Ошибка открытия файла: ' . $this->fileName);
        }
        $dst = fopen($dstFileName, 'a');

        fread($src, self::DATA_OFFSET);

        while ($chunk = fread($src, 1024 * 1024)) {
            fwrite($dst, $chunk);
        }

        fclose($dst);
        fclose($src);
    }

    /**
     * @param int $time - время в миллисекундах
     *
     * @return int - количество байт, соответствующее заданному времени
     */
    public function calculateBytesByTime($time) {
        
        $sampleRate = $this->headers['formatSampleRate'];
        $channels = $this->headers['formatChannels'];
        $bitsPerSample = $this->headers['formatBitsPerSample'];
        
        return (int)($sampleRate * $channels * ($bitsPerSample / 8) * ($time / 1000));
    }
    
    /**
     * @param string $dstFileName - путь к файлу, в который нужно записать данные
     * @param int $pauseBytes - пауза в байтах
     */
    public function exportPause($dstFileName, $pauseBytes)
    {
        $dst = fopen($dstFileName, 'a');

        fwrite($dst, str_repeat("\xff", $pauseBytes));

        fclose($dst);
    }

}
