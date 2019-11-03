<?php

declare(strict_types=1);

namespace App;

/**
 * A output buffer.
 */
class Output
{
    private $buffer = [];

    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * @param array $data [ 'packageName' => foo/bar, 'downloads' => (int) 4711 ]
     */
    public function addData(array $data)
    {
        if (empty($data)) {
            return;
        }
        $this->buffer[] = $data;
    }

    /**
     * Flush data to file.
     */
    public function flush()
    {
        $handle = fopen($this->file, 'w');
        fputcsv($handle, ['Package', 'Downloads', 'Type'], ';');

        foreach ($this->buffer as $row) {
            fputcsv($handle, [
                $row['packageName'],
                $row['downloads'],
                $row['type'],
            ],
            ';'
            );
        }

        fclose($handle);
    }
}
