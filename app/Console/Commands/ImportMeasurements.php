<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Measurement;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportMeasurements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'easyfit:import-csv
                            {file : The path to the CSV file}
                            {email : The email of the user to import measurements for}
                            {--delimiter= : CSV delimiter (default is automatically detected)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import weight measurements from a CSV file exported from Excel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $email = $this->argument('email');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return 1;
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("User not found with email: {$email}");

            return 1;
        }

        $file = fopen($filePath, 'r');
        if (! $file) {
            $this->error("Cannot open file: {$filePath}");

            return 1;
        }

        // Detect delimiter if not specified
        $delimiter = $this->option('delimiter');
        if (! $delimiter) {
            $firstLine = fgets($file);
            rewind($file);
            if ($firstLine !== false) {
                $semicolons = substr_count($firstLine, ';');
                $commas = substr_count($firstLine, ',');
                $tabs = substr_count($firstLine, "\t");

                if ($semicolons > $commas && $semicolons > $tabs) {
                    $delimiter = ';';
                } elseif ($tabs > $commas) {
                    $delimiter = "\t";
                } else {
                    $delimiter = ',';
                }
            } else {
                $delimiter = ',';
            }
        }

        $this->info("Using delimiter: '{$delimiter}'");

        // Read header
        $header = fgetcsv($file, 0, $delimiter);
        if (! $header) {
            $this->error('Failed to read CSV header.');
            fclose($file);

            return 1;
        }

        // Clean header columns
        $header = array_map(fn ($item) => strtolower(trim($item)), $header);

        // Map columns to fields (we can support both Italian and English headers)
        $columnMapping = $this->mapHeaders($header);

        if (! isset($columnMapping['date']) || ! isset($columnMapping['weight'])) {
            $this->error('CSV must contain at least a date and a weight column.');
            $this->line('Available mapping detected:');
            $this->line((string) json_encode($columnMapping, JSON_PRETTY_PRINT));
            fclose($file);

            return 1;
        }

        $imported = 0;
        $errors = 0;
        $rowCount = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                $rowCount++;

                // Combine header and row
                $data = [];
                foreach ($columnMapping as $field => $index) {
                    if (isset($row[$index])) {
                        $data[$field] = trim($row[$index]);
                    } else {
                        $data[$field] = null;
                    }
                }

                if (empty($data['date']) || empty($data['weight'])) {
                    $this->warn("Skipping row {$rowCount}: missing date or weight.");
                    $errors++;

                    continue;
                }

                // Parse date
                $parsedDate = $this->parseDate($data['date']);
                if (! $parsedDate) {
                    $this->warn("Skipping row {$rowCount}: invalid date format '{$data['date']}'.");
                    $errors++;

                    continue;
                }

                // Clean and parse floats and notes
                $weight = $this->parseDecimal($data['weight']);
                $fat = isset($data['fat_perc']) ? $this->parseDecimal($data['fat_perc']) : null;
                $muscle = isset($data['muscle_perc']) ? $this->parseDecimal($data['muscle_perc']) : null;
                $bmi = isset($data['bmi_value']) ? $this->parseDecimal($data['bmi_value']) : null;
                $notes = isset($data['notes']) ? trim($data['notes']) : null;

                if ($weight === null) {
                    $this->warn("Skipping row {$rowCount}: invalid weight value '{$data['weight']}'.");
                    $errors++;

                    continue;
                }

                // Create or update measurement
                Measurement::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $parsedDate->format('Y-m-d'),
                    ],
                    [
                        'weight' => $weight,
                        'fat_perc' => $fat,
                        'muscle_perc' => $muscle,
                        'bmi_value' => $bmi,
                        'notes' => $notes,
                    ]
                );

                $imported++;
            }

            DB::commit();
            $this->info("Import completed successfully: {$imported} records imported/updated, {$errors} errors.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to import: '.$e->getMessage());
            fclose($file);

            return 1;
        }

        fclose($file);

        return 0;
    }

    /**
     * Map header columns to model fields (supporting Italian and English).
     *
     * @param  array<int, string>  $header
     * @return array<string, int>
     */
    private function mapHeaders(array $header): array
    {
        $mapping = [];

        $dateKeys = ['date', 'data', 'giorno'];
        $weightKeys = ['weight', 'peso', 'weight_kg', 'peso_kg'];
        $fatKeys = ['fat_perc', 'fat', 'adipe', 'grasso', 'massa grassa', 'fat%', 'adipe%', 'grasso%'];
        $muscleKeys = ['muscle_perc', 'muscle', 'muscolo', 'muscoli', 'massa muscolare', 'muscle%', 'muscolo%', 'muscoli%'];
        $bmiKeys = ['bmi_value', 'bmi', 'imc'];
        $notesKeys = ['notes', 'note', 'commento', 'commenti', 'osservazioni', 'osservazione'];

        foreach ($header as $index => $colName) {
            if (in_array($colName, $dateKeys)) {
                $mapping['date'] = $index;
            } elseif (in_array($colName, $weightKeys)) {
                $mapping['weight'] = $index;
            } elseif ($this->matchesAny($colName, $fatKeys)) {
                $mapping['fat_perc'] = $index;
            } elseif ($this->matchesAny($colName, $muscleKeys)) {
                $mapping['muscle_perc'] = $index;
            } elseif (in_array($colName, $bmiKeys)) {
                $mapping['bmi_value'] = $index;
            } elseif (in_array($colName, $notesKeys)) {
                $mapping['notes'] = $index;
            }
        }

        return $mapping;
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function matchesAny(string $value, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($value === $pattern || str_contains($value, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse date from various common formats.
     */
    private function parseDate(string $dateStr): ?Carbon
    {
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'Y/m/d',
            'j/n/Y',
            'd.m.Y',
        ];

        foreach ($formats as $format) {
            try {
                // Strict parsing to match format exactly
                $parsed = Carbon::createFromFormat($format, $dateStr);
                if ($parsed !== null) {
                    $parsed = $parsed->startOfDay();
                    if ($parsed->format($format) === $dateStr) {
                        return $parsed;
                    }
                }
            } catch (\Exception $e) {
                // Ignore and try next format
            }
        }

        // Try standard Carbon parsing as fallback
        try {
            return Carbon::parse($dateStr)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse decimal from string, replacing comma with dot.
     */
    private function parseDecimal(?string $val): ?float
    {
        if ($val === null || $val === '') {
            return null;
        }

        // Remove any whitespace and convert comma to dot
        $cleaned = str_replace(',', '.', trim($val));

        // Remove non-numeric characters except dot and minus
        $cleaned = preg_replace('/[^\d\.\-]/', '', $cleaned);

        if (! is_numeric($cleaned)) {
            return null;
        }

        return (float) $cleaned;
    }
}
