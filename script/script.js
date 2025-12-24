

// Load more products
let currentPage = 1;
const productsPerPage = 8;

function loadMoreProducts() {
  const productGrid = document.querySelector(".products-grid");
  const loadMoreBtn = document.querySelector(".load-more-btn");
  
  // Show loading state
  loadMoreBtn.textContent = "Loading...";
  loadMoreBtn.disabled = true;

  // Get current filter parameters from URL
  const params = new URLSearchParams(window.location.search);
  params.set('page', currentPage + 1);
  params.set('limit', productsPerPage);

  // Fetch more products via AJAX
  fetch(window.location.pathname + '?' + params.toString())
    .then(response => response.text())
    .then(html => {
      // Extract products from the response
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const newProducts = doc.querySelectorAll('.product-card');

      if (newProducts.length === 0) {
        // No more products available
        loadMoreBtn.innerHTML = 'No more products available';
        loadMoreBtn.disabled = true;
        loadMoreBtn.style.opacity = '0.5';
        return;
      }

      // Add new products to grid
      newProducts.forEach(product => {
        productGrid.appendChild(product.cloneNode(true));
      });

      currentPage++;

      // Reset button
      loadMoreBtn.textContent = "Load More Products";
      loadMoreBtn.disabled = false;
    })
    .catch(error => {
      console.error('Error loading products:', error);
      loadMoreBtn.textContent = "Load More Products";
      loadMoreBtn.disabled = false;
    });
}


// hamburgermenu and close button

document.addEventListener("DOMContentLoaded", function () {
  const menuBtn = document.querySelector(".mobile-menu-btn");
  const closeBtn = document.querySelector(".close-menu-btn");
  const menuOverlay = document.querySelector(".mobile-menu-overlay");

  // Open menu
  menuBtn.addEventListener("click", function () {
    menuOverlay.style.display = "block";
  });

  // Close menu
  closeBtn.addEventListener("click", function () {
    menuOverlay.style.display = "none";
  });
});

// Filter button
document.addEventListener("DOMContentLoaded", function () {
  const filterBtn = document.querySelector(".filter-btn");
  const closeFilterBtn = document.querySelector(".close-filter-btn");
  const applyFilterBtn = document.querySelector(".filter-mobile-apply-btn");
  const mobileFilterSection = document.querySelector(".filter-mobile-section");

  // Open filter
  filterBtn.addEventListener("click", function () {
    mobileFilterSection.style.display = "flex";
  });

  // Close filter with close button
  closeFilterBtn.addEventListener("click", function () {
    mobileFilterSection.style.display = "none";
  });

  // Close filter with Apply button
  applyFilterBtn.addEventListener("click", function () {
    mobileFilterSection.style.display = "none";
  });
});

// thumnail product images

document.addEventListener("DOMContentLoaded", function () {
  // put all the thumbnail class in a variable
  const thumbnails = document.querySelectorAll(".thumbnail");

  // add click event to each thumb
  thumbnails.forEach((thumb) => {
    thumb.addEventListener("click", function () {
      // loop again to remove active from all - reset
      thumbnails.forEach((t) => t.classList.remove("active"));

      // Add active to clicked
      this.classList.add("active");

      //  Find the <img> tag INSIDE the clicked thumbnail
      const thumbImage = this.querySelector("img");

      // Update main image -
      document.getElementById("main-product-image").src = thumbImage.src;
    });
  });
});


// // Auto-hide welcome message after 5 seconds
// function autoHideWelcomeMessage() {
//   setTimeout(function () {
//     var message = document.getElementById("welcomeMessage");
//     if (message) {
//       message.style.display = "none";
//     }
//   }, 5000); // 5000 milliseconds = 5 seconds
// }

// // Call the function when the page loads
// document.addEventListener("DOMContentLoaded", function () {
//   autoHideWelcomeMessage();
// });



