<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Machine;
use App\Models\Reading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class MachineMonitorCommand extends Command
{
    // Signature mendefinisikan nama command & opsi
    protected $signature = 'machine:monitor
        {--setup : Run migrations and create sample machines}
        {--add-reading= : Add a reading for machine id}
        {--simulate= : Generate N random readings (default 10)}
        {--status : Show machines and latest readings}';

    protected $description = 'Monitor machines: setup, add-reading, simulate, status';

    public function handle()
    {
        // Jika user memilih --setup -> jalankan setup
        if ($this->option('setup')) {
            return $this->handleSetup();
        }

        // Jika user memilih --add-reading=ID -> panggil handler add-reading
        if ($id = $this->option('add-reading')) {
            return $this->handleAddReading((int)$id);
        }

        // Jika user memilih --simulate=N -> panggil simulate
        if ($count = $this->option('simulate')) {
            $n = (int)$count > 0 ? (int)$count : 10;
            return $this->handleSimulate($n);
        }

        // Jika user memilih --status -> tampilkan status
        if ($this->option('status')) {
            return $this->handleStatus();
        }

        // Jika tidak ada opsi, tampilkan bantuan singkat
        $this->info('Usage examples:');
        $this->line('  php artisan machine:monitor --setup');
        $this->line('  php artisan machine:monitor --add-reading=1');
        $this->line('  php artisan machine:monitor --simulate=20');
        $this->line('  php artisan machine:monitor --status');
        return 0;
    }

    // HANDLE SETUP: jalankan migrate (jika perlu) dan buat 3 mesin contoh
    protected function handleSetup()
    {
        $this->info('Running migrations (force) ...');
        // Jalankan migration programmatically — gunakan --force untuk environment non-interactive
        Artisan::call('migrate', ['--force' => true]);
        $this->info('Migration finished.');

        $this->info('Seeding sample machines...');
        // Hapus dulu jika ada (opsional untuk development)
        Machine::truncate();
        Reading::truncate();

        // Buat 3 mesin sample
        Machine::create(['name' => 'Mesin A', 'location' => 'Line 1', 'status' => 'running']);
        Machine::create(['name' => 'Mesin B', 'location' => 'Line 2', 'status' => 'running']);
        Machine::create(['name' => 'Mesin C', 'location' => 'Line 3', 'status' => 'idle']);

        $this->info('3 sample machines created: Mesin A, Mesin B, Mesin C');
        return 0;
    }

    // HANDLE ADD READING: interaktif tanya temperature & conveyor speed, validasi & simpan
    protected function handleAddReading(int $id)
    {
        $machine = Machine::find($id);
        if (!$machine) {
            $this->error("Machine with ID {$id} not found. Run --setup first.");
            return 1;
        }

        // program akan menanyakan Temperature di terminal
        $temperature = $this->ask('Masukkan Temperature (°C) [20 - 100]');

        // program akan menanyakan Conveyor speed di terminal
        $speed = $this->ask('Masukkan Conveyor Speed (m/min) [0.5 - 5.0]');

        // VALIDASI input
        if (!is_numeric($temperature) || $temperature < 20 || $temperature > 100) {
            $this->error('Temperature harus numeric antara 20 - 100.');
            return 1;
        }
        if (!is_numeric($speed) || $speed < 0.5 || $speed > 5.0) {
            $this->error('Conveyor speed harus numeric antara 0.5 - 5.0.');
            return 1;
        }

        // simpan ke DB
        Reading::create([
            'machine_id' => $machine->id,
            'temperature' => (float) $temperature,
            'conveyor_speed' => (float) $speed,
            'recorded_at' => Carbon::now(),
        ]);

        $this->info("Reading saved for {$machine->name} (ID: {$machine->id}).");

        // Peringatan ambang
        if ((float)$temperature > 80) {
            $this->warn('WARNING: Temperature > 80°C');
        }
        if ((float)$speed < 1.0) {
            $this->warn('WARNING: Conveyor speed < 1.0 m/min');
        } elseif ((float)$speed > 4.0) {
            $this->warn('WARNING: Conveyor speed > 4.0 m/min');
        }

        return 0;
    }

    // HANDLE SIMULATE: generate N pembacaan acak valid
    protected function handleSimulate(int $count)
    {
        $machines = Machine::all();
        if ($machines->isEmpty()) {
            $this->error('No machines found. Run with --setup first.');
            return 1;
        }

        $this->info("Simulating {$count} readings across {$machines->count()} machines...");

        for ($i = 0; $i < $count; $i++) {
            $machine = $machines->random();

            // generate angka acak dalam range valid
            $temperature = $this->randFloat(20, 100, 2);       // 20.00 - 100.00
            $speed = $this->randFloat(0.5, 5.0, 2);            // 0.50 - 5.00

            Reading::create([
                'machine_id' => $machine->id,
                'temperature' => $temperature,
                'conveyor_speed' => $speed,
                'recorded_at' => Carbon::now(),
            ]);
        }

        $this->info("Simulation finished: {$count} readings created.");
        return 0;
    }

    // HANDLE STATUS: tampil tabel mesin + latest reading + alerts
    protected function handleStatus()
    {
        // Eager load latestReading relation (efisien)
        $machines = Machine::with('latestReading')->get();

        if ($machines->isEmpty()) {
            $this->error('No machines available. Use --setup to create sample machines.');
            return 1;
        }

        $rows = [];
        foreach ($machines as $m) {
            $lr = $m->latestReading;
            $alerts = [];

            if ($lr) {
                if ($lr->temperature > 80) $alerts[] = 'High Temp';
                if ($lr->conveyor_speed < 1.0) $alerts[] = 'Low Speed';
                if ($lr->conveyor_speed > 4.0) $alerts[] = 'High Speed';
            }

            $rows[] = [
                $m->id,
                $m->name,
                $m->location,
                $m->status,
                $lr ? number_format($lr->temperature, 2) : '-',
                $lr ? number_format($lr->conveyor_speed, 2) : '-',
                $lr ? $lr->recorded_at->format('Y-m-d H:i:s') : '-',
                implode(', ', $alerts),
            ];
        }

        $this->table(
            ['ID','Name','Location','Status','Temp (°C)','Speed (m/min)','Recorded At','Alerts'],
            $rows
        );

        return 0;
    }

    // HELPER: buat angka float acak dengan presisi decimal
    protected function randFloat(float $min, float $max, int $decimals = 2): float
    {
        $scale = pow(10, $decimals);
        return mt_rand((int)($min * $scale), (int)($max * $scale)) / $scale;
    }

    // Commit keempat: Tambahkan fitur simulate & status report
}