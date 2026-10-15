"use strict";

var unit = document.getElementById("mobil_id");
var hari = document.getElementById("lama_hari");
var tanggalPinjam = document.getElementById("tanggal_pinjam");
var out = document.getElementById("estimasi");

function hitung() {
  if (!unit || !hari || !out) return;
  var opt = unit.options[unit.selectedIndex];
  var tarif = opt ? parseInt(opt.dataset.tarif || 0, 10) : 0;
  var n = parseInt(hari.value || 0, 10);
  out.textContent =
    tarif && n ? "Rp" + (tarif * n).toLocaleString("id-ID") : "-";
}

function rentangTersedia(encodedPeriods, mulai, durasi) {
  if (!mulai) return true;
  var akhir = new Date(mulai + "T00:00:00Z");
  akhir.setUTCDate(akhir.getUTCDate() + durasi);
  var selesai = akhir.toISOString().slice(0, 10);
  var periods = JSON.parse(encodedPeriods || "[]");
  return !periods.some(function (period) {
    if (period.status === "berjalan") return true;
    return period.start < selesai && period.end > mulai;
  });
}

function hariIniISO() {
  var now = new Date();
  return new Date(now.getTime() - now.getTimezoneOffset() * 60000)
    .toISOString()
    .slice(0, 10);
}

function perbaruiPilihanUnit() {
  if (!unit) return;
  var mulai = tanggalPinjam ? tanggalPinjam.value : "";
  var durasi = hari ? parseInt(hari.value || "1", 10) : 1;
  Array.from(unit.options).forEach(function (option) {
    if (!option.value) return;
    option.disabled = !rentangTersedia(option.dataset.periods, mulai, durasi);
  });
  if (unit.selectedOptions.length && unit.selectedOptions[0].disabled) {
    unit.value = "";
  }
  hitung();
}

if (unit) {
  unit.addEventListener("change", hitung);
}
if (hari) {
  hari.addEventListener("input", perbaruiPilihanUnit);
}
if (tanggalPinjam) {
  tanggalPinjam.addEventListener("change", perbaruiPilihanUnit);
}

var modalSewa =
  document.getElementById("modalSewa") || document.getElementById("modalPesan");
if (modalSewa) {
  modalSewa.addEventListener("show.bs.modal", function (ev) {
    var trigger = ev.relatedTarget;
    var id = trigger && trigger.dataset.mobil;
    var tanggalCari = document.getElementById("tanggalCari");
    var durasiCari = document.getElementById("durasiCari");
    if (id && unit) unit.value = id;
    if (tanggalPinjam && tanggalCari) tanggalPinjam.value = tanggalCari.value;
    if (hari && durasiCari) hari.value = durasiCari.value;
    perbaruiPilihanUnit();
  });
  modalSewa.addEventListener("shown.bs.modal", function () {
    var fokus = document.getElementById("nama_penyewa") || hari;
    if (fokus) fokus.focus();
  });
}

var fleetItems = Array.from(document.querySelectorAll(".fleet-item"));
var filterButtons = Array.from(document.querySelectorAll("[data-filter]"));
var searchInput = document.getElementById("cariMobil");
var transmissionInput = document.getElementById("filterTransmisi");
var dateInput = document.getElementById("tanggalCari");
var durationInput = document.getElementById("durasiCari");
var priceInput = document.getElementById("filterHarga");
var resultCount = document.getElementById("hasilArmada");
var statusFilter = "semua";

function cariArmada() {
  if (!fleetItems.length) return;
  var query = searchInput ? searchInput.value.trim().toLocaleLowerCase("id-ID") : "";
  var transmission = transmissionInput ? transmissionInput.value : "";
  var priceRange = priceInput ? priceInput.value : "";
  var start = dateInput ? dateInput.value : "";
  var duration = durationInput ? parseInt(durationInput.value || "1", 10) : 1;
  var availabilityDate = start || hariIniISO();
  var visible = 0;

  fleetItems.forEach(function (item) {
    var textMatches = (item.dataset.name || "")
      .toLocaleLowerCase("id-ID")
      .includes(query);
    var transmissionMatches =
      !transmission || item.dataset.transmission === transmission;
    var price = parseInt(item.dataset.price || "0", 10);
    var priceMatches =
      !priceRange ||
      (priceRange === "low" && price < 300000) ||
      (priceRange === "mid" && price >= 300000 && price <= 750000) ||
      (priceRange === "high" && price > 750000);
    var available = rentangTersedia(
      item.dataset.periods,
      availabilityDate,
      duration
    );
    var status = available ? "tersedia" : "disewa";
    item.dataset.status = status;
    var badge = item.querySelector(".availability-badge");
    if (badge) {
      badge.textContent = available ? "Tersedia" : "Jadwal penuh";
      badge.classList.toggle("status-in", available);
      badge.classList.toggle("status-out", !available);
    }
    var bookingButton = item.querySelector("[data-label-available]");
    if (bookingButton) {
      bookingButton.disabled = !available;
      bookingButton.textContent = available
        ? bookingButton.dataset.labelAvailable
        : "Jadwal penuh";
    }
    var statusMatches = statusFilter === "semua" || status === statusFilter;
    var matches =
      textMatches &&
      transmissionMatches &&
      priceMatches &&
      statusMatches;
    item.hidden = !matches;
    if (matches) visible++;
  });

  if (resultCount) {
    resultCount.textContent =
      visible === 0
        ? "Belum ada mobil yang cocok. Coba ubah kata kunci atau jadwal."
        : visible + " mobil cocok dengan pencarianmu.";
  }
}

if (unit) perbaruiPilihanUnit();

filterButtons.forEach(function (button) {
  button.addEventListener("click", function () {
    statusFilter = button.dataset.filter;
    filterButtons.forEach(function (item) {
      item.classList.toggle("active", item === button);
    });
    cariArmada();
  });
});

[searchInput, transmissionInput, dateInput, durationInput, priceInput].forEach(function (input) {
  if (input) input.addEventListener("input", cariArmada);
  if (input && input.tagName === "SELECT") {
    input.addEventListener("change", cariArmada);
  }
});

var cariSubmit = document.getElementById("cariSubmit");
if (cariSubmit) {
  cariSubmit.addEventListener("click", function () {
    cariArmada();
    document.getElementById("daftarArmada").scrollIntoView({ behavior: "smooth" });
  });
}
cariArmada();

var inputFoto = document.getElementById("foto");
var preview = document.getElementById("previewFoto");
var inputFotoEdit = document.getElementById("fotoEdit");
var previewFotoEdit = document.getElementById("previewFotoEdit");
[
  [inputFoto, preview],
  [inputFotoEdit, previewFotoEdit],
].forEach(function (pair) {
  var input = pair[0];
  var image = pair[1];
  if (input && image) {
    input.addEventListener("change", function () {
      var file = input.files[0];
      if (!file) {
        image.hidden = true;
        return;
      }
      image.src = URL.createObjectURL(file);
      image.hidden = false;
    });
  }
});

var editModal = document.getElementById("modalEditUnit");
if (editModal) {
  editModal.addEventListener("show.bs.modal", function (event) {
    var trigger = event.relatedTarget;
    if (!trigger) return;
    document.getElementById("editUnitId").value = trigger.dataset.id;
    document.getElementById("editNoPolisi").value = trigger.dataset.nopol;
    document.getElementById("editMerk").value = trigger.dataset.merk;
    document.getElementById("editTransmisi").value = trigger.dataset.transmisi;
    document.getElementById("editHarga").value = trigger.dataset.harga;
    if (inputFotoEdit) inputFotoEdit.value = "";
    if (previewFotoEdit) previewFotoEdit.hidden = true;
  });
}

var notif = document.querySelector(".notice");
if (notif) {
  setTimeout(function () {
    notif.classList.add("gone");
  }, 4500);
}
