<?php

namespace App\Console\Commands;

use App\Models\Postcode;
use App\Services\CoordinateConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class UpdatePostcodeData extends Command
{
    protected $signature = 'postcodes:update';
    protected $description = 'Update postcode data from ONS';

    private const ONS_POSTCODE_URL = 'https://api.os.uk/downloads/v1/products/CodePointOpen/downloads?area=GB&format=CSV&redirect';
    private const BATCH_SIZE = 1000;
    protected $converter;

    /**
     * Create a new command instance.
     *
     * @param CoordinateConverter $converter
     */
    public function __construct(CoordinateConverter $converter)
    {
        parent::__construct();
        $this->converter = $converter;
    }

    public function handle()
    {
        $this->info('Starting postcode update...');

        // Download and extract data
        $zipPath = storage_path('app/postcodes.zip');
        $extractPath = storage_path('app/postcodes');

        $this->downloadData($zipPath);
        $this->extractData($zipPath, $extractPath);

        // Process CSV files
        $files = glob($extractPath . '/Data/CSV/*.csv');
        $bar = $this->output->createProgressBar(count($files));

        DB::beginTransaction();

        try {
            foreach ($files as $file) {
                $this->processFile($file);
                $bar->advance();
            }

            DB::commit();
            $bar->finish();

            // Cleanup
            Storage::delete('postcodes.zip');
            Storage::deleteDirectory('postcodes');

            $this->info("\nPostcode update completed successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\nError updating postcodes: " . $e->getMessage());
        }
    }

    private function downloadData($zipPath)
    {
        $this->info('Downloading postcode data...');

        $response = Http::withOptions([
            'sink' => $zipPath
        ])->get(self::ONS_POSTCODE_URL);

        if (!$response->successful()) {
            throw new \Exception('Failed to download postcode data');
        }
    }

    private function extractData($zipPath, $extractPath)
    {
        $this->info('Extracting postcode data...');

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new \Exception('Failed to extract postcode data');
        }
    }

    private function processFile($file)
    {
        $handle = fopen($file, 'r');
        $batch = [];

        while (($data = fgetcsv($handle)) !== FALSE) {
            $coordinates = $this->converter->convertToWGS84FromEastingsNorthings($data[2], $data[3]);
            $data[2] = $coordinates['lon'];
            $data[3] = $coordinates['lat'];
            $postcode = [
                'postcode' => $data[0],
                'postcode_trimmed' => str_replace(' ', '', $data[0]),
                'quality' => $data[1],
                'longitude' => $data[2],
                'latitude' => $data[3],
                'country' => $data[4],
                'nhs_ha' => $data[5],
                'admin_county' => $data[6],
                'admin_district' => $data[7],
                'admin_ward' => $data[8],
                'location' => DB::raw("POINT($data[2], $data[3])"),
                'outcode' => substr($data[0], 0, strlen($data[0]) - 3),
                'incode' => substr($data[0], -3),
            ];


            $batch[] = $postcode;

            if (count($batch) >= self::BATCH_SIZE) {
                $this->insertBatch($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
        }

        fclose($handle);
    }

    private function insertBatch($batch)
    {
        Postcode::upsert(
            $batch,
            ['postcode'],
            array_keys($batch[0])
        );
    }
}
