Project Laravel (TKG Test) - Machine Monitor
By: Dinda Tresna Teja Nirwana
Bertujuan untuk memantau suhu mesin chamber dan kecepatan conveyor di lini produksi sepatu. Data akan disimpan di database SQLite melalui dua tabel:

machines → menyimpan informasi mesin.
readings → menyimpan pembacaan suhu dan kecepatan conveyor.
Program menyediakan 4 fitur utama:

--setup → Membuat tabel & menambahkan data mesin contoh.
--add-reading → Menambahkan data pembacaan manual untuk mesin tertentu.
--simulate → Membuat data pembacaan acak.
--status → Menampilkan daftar mesin dan data pembacaan terakhirnya.
Persiapan dan Tools yang diperlukan
Project berikut menggunakan software:

Laragon, https://laragon.org/download/

![image-20250812115644093](C:\Users\Dinda Tresna Teja\AppData\Roaming\Typora\typora-user-images\image-20250812115644093.png)

VS Code

![image-20250812115530295](C:\Users\Dinda Tresna Teja\AppData\Roaming\Typora\typora-user-images\image-20250812115530295.png)

Membuat Project Laravel di Laragon
Buka Laragon → Menu → Quick app → Laravel.
Masukkan nama project: machine-monitor→ OK.
Laragon otomatis membuat project dan virtual host C:\laragon\www\machine-monitor
Setup Database Menggunakan SQLite
Untuk setup database menggunakan SQLite, jalankan perintah di bawah ini pada terminal laragon
![image-20250812124604914](C:\Users\Dinda Tresna Teja\AppData\Roaming\Typora\typora-user-images\image-20250812124604914.png)

#membuat directory baru
mkdir database

#Buat file SQLite kosong di windows
type nul > database\database.sqlite
Kemudian lanjutkan untuk konfigurasi .env. Buka file .env di root project dan ubah bagian koneksi database menjadi:
![image-20250812125639548](C:\Users\Dinda Tresna Teja\AppData\Roaming\Typora\typora-user-images\image-20250812125639548.png)

# Mengatur jenis database yang digunakan Laravel
DB_CONNECTION=sqlite

# Menentukan lokasi file database SQLite yang akan digunakan Laravel
DB_DATABASE=database/database.sqlite
Buat Models, Migrations, Command
Laravel menyediakan Artisan CLI sebagai alat bantu untuk membuat berbagai komponen aplikasi secara otomatis. Pada tahap ini, akan membuat Model, Migration, dan Command yang diperlukan untuk membangun fitur Machine Monitor.

Perintah yang dijalankan pada terminal laragon adalah:

php artisan make:model Machine -m
php artisan make:model Reading -m
php artisan make:command MachineMonitorCommand
Penjelasan Perintah
php artisan make:model Machine -m
Fungsi: Membuat sebuah model bernama Machine beserta file migration untuk tabel yang sesuai.

File yang dihasilkan:

app/Models/Machine.php → Skeleton class model.

database/migrations/xxxx_xx_xx_create_machines_table.php → Skeleton migrasi tabel machines.

Tujuan Model: Model Machine akan merepresentasikan data mesin dalam sistem, serta digunakan untuk berinteraksi dengan tabel machines di database.

Tujuan Migration: File migration menyediakan kerangka awal untuk mendefinisikan struktur tabel machines (kolom, tipe data, relasi, indeks, dll.).

php artisan make:model Reading -m
Fungsi: Membuat sebuah model bernama Reading beserta file migration untuk tabel yang sesuai.

File yang dihasilkan:

app/Models/Reading.php → Skeleton class model.

database/migrations/xxxx_xx_xx_create_readings_table.php → Skeleton migrasi tabel readings.

Tujuan Model: Model Reading akan merepresentasikan setiap pembacaan suhu dan kecepatan conveyor dari sebuah mesin.

Tujuan Migration: File migration ini digunakan untuk menentukan struktur tabel readings, termasuk relasi foreign key ke tabel machines.

php artisan make:command MachineMonitorCommand
Fungsi: Membuat Artisan Command bernama MachineMonitorCommand.

File yang dihasilkan:

app/Console/Commands/MachineMonitorCommand.php → Skeleton class command.

Tujuan Command:

Command ini akan berisi logika utama program untuk:

Men-setup database dan membuat mesin contoh (--setup).
Menambahkan pembacaan manual (--add-reading).
Menjalankan simulasi data (--simulate).
Menampilkan status mesin (--status).
Migration
Tujuan dari migration create_machines_table.php itu adalah membuat struktur tabel machines di database yang akan digunakan Laravel untuk menyimpan data mesin. Berikut perintah yang harus diinputkan menggunakan VS Code pada xxxx_create_machine_table.php

![image-20250812134409982](C:\Users\Dinda Tresna Teja\AppData\Roaming\Typora\typora-user-images\image-20250812134409982.png)

<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration

{

  /**

   \* Run the migrations.

   */

  public function up(): void

  {

​    Schema::create('machines', function (Blueprint $table) {

​      $table->id(); // Primary key auto increment

​      $table->string('name'); // Nama mesin

​      $table->string('location'); // Lokasi mesin

​      $table->string('status')->default('running'); // Status mesin, default "running"

​      $table->timestamps(); // Kolom created_at & updated_at

​    });

  }

  /**

   \* Reverse the migrations.

   */

  public function down(): void

  {

​    Schema::dropIfExists('machines');

  }

};
Migration create_readings_table.php bertujuan untuk membuat tabel readings untuk menyimpan data pembacaan mesin yang terhubung dengan tabel machines. Berikut perintah yang harus diinputkan menggunakan VS Code pada xxxx_create_readings_table.php

<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration

{

  /**

   \* Run the migrations.

   */

  public function up(): void

  {

​    Schema::create('readings', function (Blueprint $table) {

​      $table->id(); // Primary key

​      // Relasi ke tabel machines (foreign key), jika mesin dihapus maka data pembacaannya ikut terhapus

​      $table->foreignId('machine_id')

​         ->constrained('machines')

​         ->onDelete('cascade');

​      // Data suhu mesin, format decimal 5 digit total dengan 2 angka di belakang koma (contoh: 100.00)

​      $table->decimal('temperature', 5, 2);

​      // Data kecepatan conveyor, format decimal 4 digit total dengan 2 angka di belakang koma (contoh: 5.00)

​      $table->decimal('conveyor_speed', 4, 2);

​      // Waktu pembacaan, otomatis terisi waktu sekarang jika tidak diisi

​      $table->timestamp('recorded_at')->useCurrent();

​      // Timestamps default Laravel (created_at dan updated_at)

​      $table->timestamps();

​    });

  }

  /**

   \* Reverse the migrations.

   */

  public function down(): void

  {

​    Schema::dropIfExists('readings');

  }

};
Models
Berikut perintah pada app/Models/Machine.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model

{

  use HasFactory;

  protected $fillable = [

​    'name',

​    'location',

​    'status',

  ];

  // Relasi: 1 Machine punya banyak Reading

  public function readings()

  {

​    return $this->hasMany(Reading::class);

  }

  // Relasi: 1 Machine punya 1 Reading terakhir

  public function latestReading()

  {

​    return $this->hasOne(Reading::class)->latestOfMany();

  }

}
Berikut perintah untuk app/Models/Reading.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model

{

  use HasFactory;

  protected $fillable = [

​    'machine_id',    // ID mesin yang melakukan pembacaan

​    'temperature',   // Suhu mesin

​    'conveyor_speed',  // Kecepatan conveyor

​    'recorded_at',   // Waktu pembacaan

  ];

  protected $casts = [

​    'recorded_at' => 'datetime', // Pastikan dibaca sebagai Carbon instance

  ];

  // Relasi: Reading dimiliki oleh satu Machine

  public function machine()

  {

​    return $this->belongsTo(Machine::class);

  }

}
Command lengkap
Berikut merupakan perintah untuk MachineMonitorCommand.php

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

​    {--setup : Run migrations and create sample machines}

​    {--add-reading= : Add a reading for machine id}

​    {--simulate= : Generate N random readings (default 10)}

​    {--status : Show machines and latest readings}';

  protected $description = 'Monitor machines: setup, add-reading, simulate, status';

  public function handle()

  {

​    // Jika user memilih --setup -> jalankan setup

​    if ($this->option('setup')) {

​      return $this->handleSetup();

​    }

​    // Jika user memilih --add-reading=ID -> panggil handler add-reading

​    if ($id = $this->option('add-reading')) {

​      return $this->handleAddReading((int)$id);

​    }

​    // Jika user memilih --simulate=N -> panggil simulate

​    if ($count = $this->option('simulate')) {

​      $n = (int)$count > 0 ? (int)$count : 10;

​      return $this->handleSimulate($n);

​    }

​    // Jika user memilih --status -> tampilkan status

​    if ($this->option('status')) {

​      return $this->handleStatus();

​    }

​    // Jika tidak ada opsi, tampilkan bantuan singkat

​    $this->info('Usage examples:');

​    $this->line('  php artisan machine:monitor --setup');

​    $this->line('  php artisan machine:monitor --add-reading=1');

​    $this->line('  php artisan machine:monitor --simulate=20');

​    $this->line('  php artisan machine:monitor --status');

​    return 0;

  }

  // HANDLE SETUP: jalankan migrate (jika perlu) dan buat 3 mesin contoh

  protected function handleSetup()

  {

​    $this->info('Running migrations (force) ...');

​    // Jalankan migration programmatically — gunakan --force untuk environment non-interactive

​    Artisan::call('migrate', ['--force' => true]);

​    $this->info('Migration finished.');

​    $this->info('Seeding sample machines...');

​    // Hapus dulu jika ada (opsional untuk development)

​    Machine::truncate();

​    Reading::truncate();

​    // Buat 3 mesin sample

​    Machine::create(['name' => 'Mesin A', 'location' => 'Line 1', 'status' => 'running']);

​    Machine::create(['name' => 'Mesin B', 'location' => 'Line 2', 'status' => 'running']);

​    Machine::create(['name' => 'Mesin C', 'location' => 'Line 3', 'status' => 'idle']);

​    $this->info('3 sample machines created: Mesin A, Mesin B, Mesin C');

​    return 0;

  }

  // HANDLE ADD READING: interaktif tanya temperature & conveyor speed, validasi & simpan

  protected function handleAddReading(int $id)

  {

​    $machine = Machine::find($id);

​    if (!$machine) {

​      $this->error("Machine with ID {$id} not found. Run --setup first.");

​      return 1;

​    }

​    // program akan menanyakan Temperature di terminal

​    $temperature = $this->ask('Masukkan Temperature (°C) [20 - 100]');

​    // program akan menanyakan Conveyor speed di terminal

​    $speed = $this->ask('Masukkan Conveyor Speed (m/min) [0.5 - 5.0]');

​    // VALIDASI input

​    if (!is_numeric($temperature) || $temperature < 20 || $temperature > 100) {

​      $this->error('Temperature harus numeric antara 20 - 100.');

​      return 1;

​    }

​    if (!is_numeric($speed) || $speed < 0.5 || $speed > 5.0) {

​      $this->error('Conveyor speed harus numeric antara 0.5 - 5.0.');

​      return 1;

​    }

​    // simpan ke DB

​    Reading::create([

​      'machine_id' => $machine->id,

​      'temperature' => (float) $temperature,

​      'conveyor_speed' => (float) $speed,

​      'recorded_at' => Carbon::now(),

​    ]);

​    $this->info("Reading saved for {$machine->name} (ID: {$machine->id}).");

​    // Peringatan ambang

​    if ((float)$temperature > 80) {

​      $this->warn('WARNING: Temperature > 80°C');

​    }

​    if ((float)$speed < 1.0) {

​      $this->warn('WARNING: Conveyor speed < 1.0 m/min');

​    } elseif ((float)$speed > 4.0) {

​      $this->warn('WARNING: Conveyor speed > 4.0 m/min');

​    }

​    return 0;

  }

  // HANDLE SIMULATE: generate N pembacaan acak valid

  protected function handleSimulate(int $count)

  {

​    $machines = Machine::all();

​    if ($machines->isEmpty()) {

​      $this->error('No machines found. Run with --setup first.');

​      return 1;

​    }

​    $this->info("Simulating {$count} readings across {$machines->count()} machines...");

​    for ($i = 0; $i < $count; $i++) {

​      $machine = $machines->random();

​      // generate angka acak dalam range valid

​      $temperature = $this->randFloat(20, 100, 2);    // 20.00 - 100.00

​      $speed = $this->randFloat(0.5, 5.0, 2);       // 0.50 - 5.00

​      Reading::create([

​        'machine_id' => $machine->id,

​        'temperature' => $temperature,

​        'conveyor_speed' => $speed,

​        'recorded_at' => Carbon::now(),

​      ]);

​    }

​    $this->info("Simulation finished: {$count} readings created.");

​    return 0;

  }

  // HANDLE STATUS: tampil tabel mesin + latest reading + alerts

  protected function handleStatus()

  {

​    // Eager load latestReading relation (efisien)

​    $machines = Machine::with('latestReading')->get();

​    if ($machines->isEmpty()) {

​      $this->error('No machines available. Use --setup to create sample machines.');

​      return 1;

​    }

​    $rows = [];

​    foreach ($machines as $m) {

​      $lr = $m->latestReading;

​      $alerts = [];

​      if ($lr) {

​        if ($lr->temperature > 80) $alerts[] = 'High Temp';

​        if ($lr->conveyor_speed < 1.0) $alerts[] = 'Low Speed';

​        if ($lr->conveyor_speed > 4.0) $alerts[] = 'High Speed';

​      }

​      $rows[] = [

​        $m->id,

​        $m->name,

​        $m->location,

​        $m->status,

​        $lr ? number_format($lr->temperature, 2) : '-',

​        $lr ? number_format($lr->conveyor_speed, 2) : '-',

​        $lr ? $lr->recorded_at->format('Y-m-d H:i:s') : '-',

​        implode(', ', $alerts),

​      ];

​    }

​    $this->table(

​      ['ID','Name','Location','Status','Temp (°C)','Speed (m/min)','Recorded At','Alerts'],

​      $rows

​    );

​    return 0;

  }

  // HELPER: buat angka float acak dengan presisi decimal

  protected function randFloat(float $min, float $max, int $decimals = 2): float

  {

​    $scale = pow(10, $decimals);

​    return mt_rand((int)($min * $scale), (int)($max * $scale)) / $scale;

  }

  // Commit keempat: Tambahkan fitur simulate & status report

}
Test
![image-20250812140430074](C:\Users\Dinda Tresna Teja\AppData\Roaming\Typora\typora-user-images\image-20250812140430074.png)

![image-20250812140632717](C:\Users\Dinda Tresna Teja\AppData\Roaming\Typora\typora-user-images\image-20250812140632717.png)

![image-20250812140510602](C:\Users\Dinda Tresna Teja\AppData\Roaming\Typora\typora-user-images\image-20250812140510602.png)

![image-20250812140710582](C:\Users\Dinda Tresna Teja\AppData\Roaming\Typora\typora-user-images\image-20250812140710582.png)
