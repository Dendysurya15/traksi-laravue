export interface LaporanP2H {
    id: number;
    jenis_unit: string;
    unit_kerja: string;
    app_version: string;
    foto_unit: string;
    kerusakan_unit: string; // This will be parsed
    kode_unit: string;
    lat: string;
    lon: string;
    status_unit_beroperasi: string;
    tanggal_upload: string;
    user: string;
}
