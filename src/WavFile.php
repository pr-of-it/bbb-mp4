<?php

namespace ProfIT\Bbb;

/**
 * Class WavFile
 * @package ProfIT\Bbb
 */
class WavFile
{
    protected $headerFormat = [
        'chunkId'       => 'V',
        'chunkSize'     => 'V',
        'format'        => 'V',
        'subchunk1Id'   => 'V',
        'subchunk1Size' => 'V',
        'audioFormat'   => 'v',
        'numChannels'   => 'v',
        'sampleRate'    => 'V',
        'byteRate'      => 'V',
        'blockAlign'    => 'v',
        'bitsPerSample' => 'v',
        'subchunk2Id'   => 'V',
        'subchunk2Size' => 'V',
    ];
    
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
    public function unpackHeaders($headersPacked) {
        $unpackFormat = [];
        foreach ($this->headerFormat as $k => $v) {
            $unpackFormat[] = $v . $k;
        }
        return unpack(implode('/', $unpackFormat), $headersPacked);
    }

    /**
     * @param string $headers - заголовки в виде ассоциативного массива
     *
     * @return array - заголовки в сыром виде
     */
    public function packHeaders($headers) {
        $packFormat = implode('', $this->headerFormat);
        return call_user_func_array('pack', array_merge([$packFormat], $headers));
    }

    /**
     * @return array - заголовки в сыром виде
     *
     * @throws \ProfIT\Bbb\Exception
     */
    public function getHeadersPacked()
    {
        $src = fopen($this->fileName, 'r');
        if (false === $src) {
            throw new \ProfIT\Bbb\Exception ('Ошибка открытия файла: ' . $this->fileName);
        }

        $headersPacked = fread($src, 44);
        fclose($src);

        return $headersPacked;
    }

    /**
     * @return array - заголовки в виде ассоциативного массива
     */
    public function getHeaders()
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

        fread($src, 44);

        while ($chunk = fread($src, 1024 * 1024)) {
            fwrite($dst, $chunk);
        }

        fclose($dst);
        fclose($src);
    }

}