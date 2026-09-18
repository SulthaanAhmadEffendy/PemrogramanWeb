// ===== Hamburger menu (JS-driven, menggantikan checkbox hack) =====
function initNavToggle() {
  const toggleBtn = document.getElementById("nav-toggle-btn");
  const nav = document.querySelector("header nav");
  if (!toggleBtn || !nav) return;

  toggleBtn.addEventListener("click", function () {
    nav.classList.toggle("nav-open");
  });
}

// ===== Konfirmasi hapus (front-end only, belum ke server) =====
function initHapusConfirm() {
  document.querySelectorAll(".btn-hapus").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const row = btn.closest("tr");
      const nama = row ? row.querySelector("td")?.textContent : "data ini";
      const yakin = confirm('Yakin ingin menghapus "' + nama + '"?');
      if (yakin && row) {
        row.remove();
        updateTableCount();
      }
    });
  });
}

function updateTableCount(rows, visibleRows) {
  const table = document.querySelector(".table-responsive table");
  if (!table) return;

  let count = document.querySelector(".table-count");
  if (!count) {
    count = document.createElement("p");
    count.className = "table-count";
    count.setAttribute("aria-live", "polite");
    table
      .closest(".table-responsive")
      .insertAdjacentElement("beforebegin", count);
  }

  const allRows = rows || table.querySelectorAll("tbody tr");
  const shownRows =
    visibleRows ||
    Array.from(allRows).filter(function (row) {
      return row.style.display !== "none";
    });
  const heading = document.querySelector("section h2")?.textContent || "Data";
  const label = heading.replace(/^Daftar\s+/i, "").toLowerCase();
  count.textContent =
    "Menampilkan " + shownRows.length + " dari " + allRows.length + " " + label;
}

// ===== Filter/pencarian tabel real-time =====
function initTableFilter() {
  const input = document.getElementById("search-input");
  const table = document.querySelector(".table-responsive table");
  if (!input || !table) return;

  const columnIndex = Number(table.dataset.filterColumn || 0);

  function filterRows() {
    const keyword = input.value.toLowerCase();
    const rows = table.querySelectorAll("tbody tr");
    rows.forEach(function (row) {
      const cell = row.querySelectorAll("td")[columnIndex];
      const teks = cell ? cell.textContent.toLowerCase() : "";
      row.style.display = teks.includes(keyword) ? "" : "none";
    });
    updateTableCount(rows);
  }

  input.addEventListener("input", filterRows);
  updateTableCount();
}

// ===== Validasi form (client-side) =====
function tampilkanError(input, pesan) {
  hapusError(input);
  const span = document.createElement("span");
  span.className = "error";
  span.textContent = pesan;
  input.insertAdjacentElement("afterend", span);
}

function hapusError(input) {
  const next = input.nextElementSibling;
  if (next && next.classList.contains("error")) {
    next.remove();
  }
}

function initValidasiForm() {
  const form = document.getElementById("form-tambah");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    let valid = true;

    const aturanValidasi = [
      {
        selector: "[name='judul'], [name='nama']",
        isInvalid: function (input) {
          return input.value.trim() === "";
        },
        pesan: "Field ini wajib diisi.",
      },
      {
        selector: "[name='pengarang']",
        isInvalid: function (input) {
          return input.value.trim() === "";
        },
        pesan: "Pengarang wajib diisi.",
      },
      {
        selector: "[name='tahun']",
        isInvalid: function (input) {
          const nilai = parseInt(input.value, 10);
          return isNaN(nilai) || nilai < 1900 || nilai > 2026;
        },
        pesan: "Tahun harus di antara 1900-2026.",
      },
      {
        selector: "[name='stok']",
        isInvalid: function (input) {
          const nilai = parseInt(input.value, 10);
          return isNaN(nilai) || nilai < 0;
        },
        pesan: "Stok tidak boleh negatif.",
      },
      {
        selector: "[name='isbn']",
        isInvalid: function (input) {
          return (
            input.value.trim() !== "" && !/^[0-9-]+$/.test(input.value.trim())
          );
        },
        pesan: "ISBN hanya boleh berisi angka dan tanda hubung.",
      },
    ];

    aturanValidasi.forEach(function (aturan) {
      const input = form.querySelector(aturan.selector);
      if (!input) return;

      if (aturan.isInvalid(input)) {
        tampilkanError(input, aturan.pesan);
        valid = false;
      } else {
        hapusError(input);
      }
    });

    if (!valid) {
      e.preventDefault();
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  initNavToggle();
  initHapusConfirm();
  initTableFilter();
  initValidasiForm();
});
