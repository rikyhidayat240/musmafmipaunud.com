<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'nim' => '2008511001',
            'id_program_studi' => 1,
            'nama' => 'Admin DPM FMIPA',
            'angkatan' => 2021,
            'email' => 'dpmfmipaunud2025@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'is_admin' => true
        ]);
        User::factory()->createMany([
            ["nim" => "2208511021", "nama" => "Ni Putu Wulan Kusuma Dewi", "angkatan" => 2022, "id_program_studi" => 1],
            ["nim" => "2308551049", "nama" => "Kadek Bintang Arya Mahardika", "angkatan" => 2023, "id_program_studi" => 5],
            ["nim" => "2308511034", "nama" => "Nur Rifatul Ifadah", "angkatan" => 2023, "id_program_studi" => 1],
            ["nim" => "2208551079", "nama" => "Ni Wayan Nanda Nirmala Sari", "angkatan" => 2022, "id_program_studi" => 5],
            ["nim" => "2408551063", "nama" => "I Gusti Ayu Adhelia Mona Prayumita", "angkatan" => 2024, "id_program_studi" => 5],
            ["nim" => "2408521029", "nama" => "I Putu Kenny Bayu Adi Putra", "angkatan" => 2024, "id_program_studi" => 2],
            ["nim" => "2408521004", "nama" => "Dewa Ayu Dwi Anjani", "angkatan" => 2024, "id_program_studi" => 2],
            ["nim" => "2408541022", "nama" => "Putu Ayu Mas Ariesty Paramasuari", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2308511015", "nama" => "Almira Nathania", "angkatan" => 2023, "id_program_studi" => 1],
            ["nim" => "2308511031", "nama" => "Ratna Iren Br Tamba", "angkatan" => 2023, "id_program_studi" => 1],
            ["nim" => "2408551053", "nama" => "Putu Kirani Prasanti Dewi", "angkatan" => 2024, "id_program_studi" => 5],
            ["nim" => "2308511048", "nama" => "Ni Made Nari Panditani", "angkatan" => 2023, "id_program_studi" => 1],
            ["nim" => "2408531033", "nama" => "I Gusti Ayu Komang Eriyani Maheswari", "angkatan" => 2024, "id_program_studi" => 3],
            ["nim" => "2408541057", "nama" => "Matthew Yunot Eloney Saragih", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408551049", "nama" => "Ni Made Yulita Prabawati", "angkatan" => 2024, "id_program_studi" => 5],
            ["nim" => "2408511036", "nama" => "Ni Made Mia Ariastini", "angkatan" => 2024, "id_program_studi" => 1],
            ["nim" => "2408541004", "nama" => "Devina Putri Indraswari", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408561149", "nama" => "Cindy Rodenni Siringoringo", "angkatan" => 2024, "id_program_studi" => 6],
            ["nim" => "2408551005", "nama" => "Nyoman Dania Sri Manika", "angkatan" => 2024, "id_program_studi" => 5],
            ["nim" => "2408551035", "nama" => "Ni Putu Revalina Suryani Dewi", "angkatan" => 2024, "id_program_studi" => 5],
            ["nim" => "2408541077", "nama" => "Ni Putu Ayu Diah Wulandari", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408561006", "nama" => "Ni Luh Putu Arvina Indira Arda", "angkatan" => 2024, "id_program_studi" => 6],
            ["nim" => "2408561016", "nama" => "Asa Attalya Amazia Sembiring", "angkatan" => 2024, "id_program_studi" => 6],
            ["nim" => "2308551042", "nama" => "I Putu Rakha Teja Wistara", "angkatan" => 2023, "id_program_studi" => 5],
            ["nim" => "2408561086", "nama" => "I Wayan Gde Adi Surya Wirawan", "angkatan" => 2024, "id_program_studi" => 6],
            ["nim" => "2308541043", "nama" => "Made Andini Maharani", "angkatan" => 2023, "id_program_studi" => 4],
            ["nim" => "2408561048", "nama" => "I Gede Arya Kesha Narendra Subirman", "angkatan" => 2024, "id_program_studi" => 6],
            ["nim" => "2408541040", "nama" => "I Putu Suartawan", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408561078", "nama" => "Ni Kadek Evy Wiramitha", "angkatan" => 2024, "id_program_studi" => 6],
            ["nim" => "2408531068", "nama" => "Grace Arabela Kallem", "angkatan" => 2024, "id_program_studi" => 3],
            ["nim" => "2408511040", "nama" => "Shafa Magista Puspaningrum", "angkatan" => 2024, "id_program_studi" => 1],
            ["nim" => "2408541043", "nama" => "Anak Agung Made Jayanatha Suputra", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408561102", "nama" => "Richardo Raphael Wibisono", "angkatan" => 2024, "id_program_studi" => 6],
            ["nim" => "2408541036", "nama" => "Ni Putu Ananda Artasari Cahyani", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408521018", "nama" => "Ni Ketut Sri Kawari", "angkatan" => 2024, "id_program_studi" => 2],
            ["nim" => "2408521013", "nama" => "I Made Widnyana", "angkatan" => 2024, "id_program_studi" => 2],
            ["nim" => "2408561090", "nama" => "Mochammad Riky Hidayat", "angkatan" => 2024, "id_program_studi" => 6],
            ["nim" => "2408541051", "nama" => "Muhammad Abdillah", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408541084", "nama" => "Kadek Aby Nugraha Kusuma", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408561096", "nama" => "I Made Galang Mahessa Mahutama", "angkatan" => 2024, "id_program_studi" => 6],
            ["nim" => "2308541052", "nama" => "Ryo Alesandro Sagala", "angkatan" => 2023, "id_program_studi" => 4],
            ["nim" => "2408541005", "nama" => "Gusti Ayu Agung Ganaya Arsita", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408541013", "nama" => "Ni Komang Leonyta Agustiya", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2408541021", "nama" => "Anak Agung Ayu Shanty Mahesuari Gayatri", "angkatan" => 2024, "id_program_studi" => 4],
            ["nim" => "2308511022", "nama" => "Ni Ketut Widhi Widanty Tirthaningsih", "angkatan" => 2023, "id_program_studi" => 1],
            // tester admin anomali
            ["nim" => "2308561070", "nama" => "I Made Gede Aryadana Baraja Putra", "angkatan" => 2023, "id_program_studi" => 6],
            ["nim" => "2308561015", "nama" => "I Made Aditya", "angkatan" => 2023, "id_program_studi" => 6],
            ["nim" => "2308561010", "nama" => "William Saputra", "angkatan" => 2023, "id_program_studi" => 6],
            ["nim" => "2308561118", "nama" => "I Ketut Cahaya Tirta Dharma Putra", "angkatan" => 2023, "id_program_studi" => 6],
        ]);
    }
}
