import React, { useState, useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';


export default function Laporan({ auth, dailyReport, filters }) {
  const [startDate, setStartDate] = useState(filters.start_date || new Date().toISOString().slice(0,10));
  const [endDate, setEndDate] = useState(filters.end_date || new Date().toISOString().slice(0,10));
  const [dataType, setDataType] = useState(filters.tipe || 'semua');

  const firstRender = useRef(true);

  useEffect(() => {
    if (firstRender.current) {
      firstRender.current = false;
      return;
    }
    router.get('/dashboard/admin/inventory/laporan', {
      start_date: startDate,
      end_date: endDate,
      tipe: dataType === 'semua' ? undefined : dataType,
    }, {
      preserveScroll: true,
      preserveState: true,
      replace: true,
    });
  }, [startDate, endDate, dataType]);

  const filterData = () => {
    return dailyReport.map((row) => ({
      label: row.tanggal,
      masuk: dataType !== 'keluar' ? row.masuk : 0,
      keluar: dataType !== 'masuk' ? row.keluar : 0,
      pendapatan: row.pendapatan,
    }));
  };

  const generatePdf = () => {
    const filtered = filterData();
    const tableBody = [
      [
        { text: 'Tanggal', style: 'tableHeader' },
        ...(dataType !== 'keluar' ? [{ text: 'Barang Masuk', style: 'tableHeader' }] : []),
        ...(dataType !== 'masuk' ? [{ text: 'Barang Keluar', style: 'tableHeader' }] : []),
        { text: 'Pendapatan', style: 'tableHeader' },
      ],
    ];

    filtered.forEach((row) => {
      tableBody.push([
        row.label,
        ...(dataType !== 'keluar' ? [row.masuk.toString()] : []),
        ...(dataType !== 'masuk' ? [row.keluar.toString()] : []),
        `Rp ${row.pendapatan.toLocaleString('id-ID')}`,
      ]);
    });

    const docDefinition = {
      content: [
        { text: 'Laporan Inventory Harian', style: 'header' },
        {
          style: 'tableExample',
          table: {
            widths: Array(tableBody[0].length).fill('*'),
            body: tableBody,
          },
          layout: {
            fillColor: (rowIndex) => (rowIndex % 2 === 0 ? '#CCCCCC' : null),
          },
        },
      ],
      styles: {
        header: {
          fontSize: 18,
          bold: true,
          marginBottom: 15,
        },
        tableExample: {
          margin: [0, 5, 0, 15],
        },
        tableHeader: {
          bold: true,
          fontSize: 13,
          color: 'black',
          fillColor: '#eeeeee',
          margin: [0, 5, 0, 5],
        },
      },
    };

    const fileName = `laporan_inventory_${startDate}_to_${endDate}_${dataType}.pdf`;
    pdfMake.createPdf(docDefinition).download(fileName);
  };

  return (
    <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800">Laporan Inventory Harian</h2>}>
      <div className="p-6 max-w-4xl mx-auto bg-white rounded shadow space-y-4">
        {/* Filter Controls */}
        <div className="flex flex-wrap items-center gap-4 mb-4">
          <div>
            <label className="block text-sm font-medium">Tanggal Mulai</label>
            <input
              type="date"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
              className="border rounded p-2"
              max={endDate}
            />
          </div>
          <div>
            <label className="block text-sm font-medium">Tanggal Akhir</label>
            <input
              type="date"
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
              className="border rounded p-2"
              min={startDate}
              max={new Date().toISOString().slice(0,10)}
            />
          </div>
          <div>
            <label className="block text-sm font-medium">Jenis Data</label>
            <select
              value={dataType}
              onChange={(e) => setDataType(e.target.value)}
              className="border rounded p-2"
            >
              <option value="semua">Semua</option>
              <option value="masuk">Barang Masuk</option>
              <option value="keluar">Barang Keluar</option>
            </select>
          </div>
        </div>

        {/* Table */}
        <table className="w-full border table-auto mb-4">
          <thead className="bg-gray-100">
            <tr>
              <th className="p-2 text-left">Tanggal</th>
              {dataType !== 'keluar' && <th className="p-2 text-left">Barang Masuk</th>}
              {dataType !== 'masuk' && <th className="p-2 text-left">Barang Keluar</th>}
              <th className="p-2 text-left">Pendapatan</th>
            </tr>
          </thead>
          <tbody>
            {filterData().length === 0 ? (
              <tr>
                <td colSpan="4" className="p-4 text-center text-gray-500">Tidak ada data.</td>
              </tr>
            ) : (
              filterData().map((row) => (
                <tr key={row.label} className="border-t">
                  <td className="p-2">{row.label}</td>
                  {dataType !== 'keluar' && <td className="p-2">{row.masuk}</td>}
                  {dataType !== 'masuk' && <td className="p-2">{row.keluar}</td>}
                  <td className="p-2">Rp {row.pendapatan.toLocaleString('id-ID')}</td>
                </tr>
              ))
            )}
          </tbody>
        </table>

        {/* PDF Button */}
        <button onClick={generatePdf} className="bg-blue-600 text-white px-4 py-2 rounded">
          Download Laporan PDF
        </button>
      </div>
    </AuthenticatedLayout>
  );
}
