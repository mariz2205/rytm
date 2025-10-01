// sample product list for UI testing
let products = [
  { id: 1, name: "Acoustic Guitar", price: 5000.00, stock: 10, category: "instruments", description: "A basic acoustic" },
  { id: 2, name: "Electric Guitar", price: 8500.00, stock: 5, category: "instruments", description: "Electric, with amp" }
];

// If you want persistence in localStorage during testing:
if (localStorage.getItem("rytm_products")) {
  products = JSON.parse(localStorage.getItem("rytm_products"));
} else {
  localStorage.setItem("rytm_products", JSON.stringify(products));
}
