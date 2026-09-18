// Loader generik untuk data buku dan anggota.
async function muatDaftar(namaFile, daftarKunci) {
  const table = document.querySelector(".table-responsive table");
  const tbody = table?.querySelector("tbody");
  const loading = document.getElementById("loading-indicator");
  if (!tbody || !table) return;

  if (loading) loading.style.display = "block";
  tbody.innerHTML = "";

  try {
    // Simulasi koneksi lambat agar loading indicator terlihat.
    await new Promise((resolve) => setTimeout(resolve, 3000));

    const res = await fetch(namaFile);
    if (!res.ok) {
      throw new Error("Gagal mengambil data (status " + res.status + ")");
    }
    const daftarData = await res.json();

    daftarData.forEach(function (data) {
      const tr = document.createElement("tr");
      daftarKunci.forEach(function (kunci) {
        const td = document.createElement("td");
        td.textContent = data[kunci] ?? "";
        tr.appendChild(td);
      });

      const aksi = document.createElement("td");
      aksi.innerHTML =
        '<button type="button">Edit</button> ' +
        '<button type="button" class="btn-hapus">Hapus</button>';
      tr.appendChild(aksi);
      tbody.appendChild(tr);
    });
  } catch (err) {
    tbody.innerHTML =
      '<tr><td colspan="' +
      (daftarKunci.length + 1) +
      '">Gagal memuat data: ' +
      err.message +
      "</td></tr>";
  } finally {
    if (loading) loading.style.display = "none";
  }
}

function muatDaftarBuku() {
  return muatDaftar("../data/buku.json", [
    "judul",
    "pengarang",
    "kategori",
    "tahun",
    "stok",
  ]);
}

function muatDaftarAnggota() {
  return muatDaftar("../data/anggota.json", [
    "no_anggota",
    "nama",
    "alamat",
    "no_hp",
  ]);
}

document.addEventListener("DOMContentLoaded", function () {
  const table = document.querySelector(".table-responsive table");
  if (!table) return;

  if (table.dataset.jenis === "buku") {
    document
      .getElementById("btn-muat-ulang")
      ?.addEventListener("click", muatDaftarBuku);
    muatDaftarBuku();
  } else if (table.dataset.jenis === "anggota") {
    muatDaftarAnggota();
  }
});