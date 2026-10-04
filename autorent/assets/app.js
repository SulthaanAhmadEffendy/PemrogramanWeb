var unit = document.getElementById("mobil_id");
var hari = document.getElementById("lama_hari");
var out = document.getElementById("estimasi");

function hitung() {
  if (!unit || !hari || !out) return;
  var opt = unit.options[unit.selectedIndex];
  var tarif = opt ? parseInt(opt.dataset.tarif || 0, 10) : 0;
  var n = parseInt(hari.value || 0, 10);
  out.textContent =
    tarif && n ? "Rp" + (tarif * n).toLocaleString("id-ID") : "-";
}
if (unit && hari) {
  unit.addEventListener("change", hitung);
  hari.addEventListener("input", hitung);
}

var modalSewa =
  document.getElementById("modalSewa") || document.getElementById("modalPesan");
if (modalSewa) {
  modalSewa.addEventListener("show.bs.modal", function (ev) {
    var id = ev.relatedTarget && ev.relatedTarget.dataset.mobil;
    if (id) unit.value = id;
    hitung();
  });
  modalSewa.addEventListener("shown.bs.modal", function () {
    var fokus = document.getElementById("nama_penyewa") || hari;
    if (fokus) fokus.focus();
  });
}

document.querySelectorAll("[data-filter]").forEach(function (tombol) {
  tombol.addEventListener("click", function () {
    var f = tombol.dataset.filter;
    document.querySelectorAll("[data-filter]").forEach(function (t) {
      t.classList.toggle("active", t === tombol);
    });
    document.querySelectorAll("[data-status]").forEach(function (k) {
      k.hidden = f !== "semua" && k.dataset.status !== f;
    });
  });
});

var inputFoto = document.getElementById("foto");
var preview = document.getElementById("previewFoto");
if (inputFoto && preview) {
  inputFoto.addEventListener("change", function () {
    var f = inputFoto.files[0];
    if (!f) {
      preview.hidden = true;
      return;
    }
    preview.src = URL.createObjectURL(f);
    preview.hidden = false;
  });
}

var notif = document.querySelector(".notice");
if (notif)
  setTimeout(function () {
    notif.classList.add("gone");
  }, 4500);
