<?php
include 'connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Don Macchaitos</title>
  <link rel="icon" href="admin/img/images.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
      color: white;
    }
    .navbar {
      background-color: #1c1c3c;
    }
    .navbar a, .navbar-brand, .nav-link, h1, h2, span, p {
      color: white !important;
    }
    .card-img-top {
  transition: transform 0.3s ease;
  cursor: pointer;
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.card-img-top:hover {
  transform: scale(1.1);
  z-index: 2;
}

    .card {
      background-color: #1e1e2f;
      border: none;
      color: white;
    }
    .btn-primary {
      background-color: #007bff;
      border: none;
    }
    .btn-primary:hover {
      background-color: #0056b3;
    }
    #chatbot-window, #feedback-button {
      position: fixed;
      bottom: 20px;
      z-index: 999;
    }
    #chatbot-window {
      right: 20px;
      width: 300px;
      display: none;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.5);
      overflow: hidden;
      color: black;
    }
    #feedback-button {
      left: 20px;
      background: #6b4f4f;
      color: white;
      padding: 10px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
    }
    #chat-button {
      position: fixed;
      bottom: 80px;
      right: 20px;
      background: #6b4f4f;
      color: black;
      padding: 10px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
    }
    /* Image Modal Style */
    #imageModal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    #modalImage {
      max-width: 90%;
      max-height: 90%;
      margin: auto;
      display: block;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
      <a class="navbar-brand" href="#">Don Macchaitos</a>
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart (<span id="cart-count">0</span>)</a></li>
        <li class="nav-item"><a class="nav-link" href="admin/login.php">Admin</a></li>
      </ul>
    </div>
  </nav>

  <div class="container mt-4">
    <h1 class="text-center mb-4">Choose Your Ice Coffee</h1>
    <div class="row">
      <?php
      $products = $conn->query("SELECT * FROM product_list");
      while ($row = $products->fetch_assoc()): ?>

        <div class="col-md-3 mb-4">
          <div class="card h-100">
            <img src="img/<?= htmlspecialchars($row["img_path"]) ?>" class="card-img-top" alt="<?= htmlspecialchars($row["name"]) ?>" onerror="this.onerror=null; this.src='img/default.png';" onclick="openModal(this.src)">
            <div class="card-body text-center">
              <h5 class="card-title"><?= htmlspecialchars($row["name"]) ?></h5>
              <p class="card-text">Php <?= number_format($row["price"], 2) ?></p>
              <p class="card-text available-qty">Available: <?= $row["qty"] > 0 ? $row["qty"] : "<span style='color:red;'>Out of Stock</span>"; ?></p>
              <input type="number" class="form-control mb-2 quantity" value="0" min="1" max="<?= $row["qty"] ?>" <?= $row["qty"] <= 0 ? "disabled" : ""; ?>>
              <button class="btn btn-primary add-to-cart w-100"
                      data-name="<?= htmlspecialchars($row["name"]) ?>"
                      data-price="<?= $row["price"] ?>"
                      data-img="<?= htmlspecialchars($row["img_path"]) ?>"
                      data-qty="<?= $row["qty"] ?>"
                      <?= $row["qty"] <= 0 ? "disabled" : ""; ?>>Add to Cart</button>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <div id="chatbot-window">
    <div style="background:#6b4f4f; color:white; padding:10px; border-top-left-radius:10px; border-top-right-radius:10px; display:flex; justify-content:space-between; align-items:center;">
      <span>Don Mac Chat-Bot 🤖</span>
      <button onclick="toggleChat()" style="background:none; border:none; color:white; font-size:20px;">×</button>
    </div>
    <div id="chat-content" style="padding:20px; height:250px; text-align: center; overflow-y:auto; font-size:14px;  background: linear-gradient(to right, #2c5364, #203a43, #0f2027); color:white;"></div>
    <div style="display:flex; border-top:1px solid #ccc;">
      <input id="chat-input" type="text" placeholder="Ask me anything..." style="flex:1; padding:8px; border:none;">
      <button onclick="sendMessage()" style="padding:8px 12px; background:#6b4f4f; color:white; border:none;">Send</button>
    </div>
  </div>

  <button id="feedback-button" onclick="showFeedbackForm()">📝 Feedback</button>
  <button id="chat-button" onclick="toggleChat()">💬</button>

  <!-- Image Modal -->
  <div id="imageModal" onclick="closeModal()">
    <img id="modalImage" src="" />
  </div>

  <script>
    function openModal(src) {
      document.getElementById("imageModal").style.display = "block";
      document.getElementById("modalImage").src = src;
    }

    function closeModal() {
      document.getElementById("imageModal").style.display = "none";
    }

    document.addEventListener("DOMContentLoaded", function () {
      updateCartCount();
      document.addEventListener("click", function (event) {
        if (event.target.classList.contains("add-to-cart")) {
          let button = event.target;
          let itemName = button.getAttribute("data-name");
          let itemPrice = button.getAttribute("data-price");
          let itemImg = button.getAttribute("data-img");
          let maxQty = parseInt(button.getAttribute("data-qty"));
          let qtyInput = button.previousElementSibling;
          let itemQty = qtyInput ? parseInt(qtyInput.value) : 1;
          let availableSpan = button.parentElement.querySelector(".available-qty");

          if (!itemQty || itemQty === 0) {
            Swal.fire("Invalid Quantity", "Please input a quantity.", "warning");
            return;
          }

          if (maxQty <= 0) {
            Swal.fire("Out of Stock!", `${itemName} is currently unavailable.`, "error");
            return;
          }

          if (itemQty > maxQty) {
            Swal.fire("Not Enough Stock!", `Only ${maxQty} items of ${itemName} are available.`, "warning");
            qtyInput.value = maxQty;
            return;
          }

          fetch("cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=add&name=${encodeURIComponent(itemName)}&price=${encodeURIComponent(itemPrice)}&qty=${encodeURIComponent(itemQty)}&img=${encodeURIComponent(itemImg)}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === "success") {
              Swal.fire("Added to Cart!", `${itemQty} x ${itemName} added.`, "success");
              updateCartCount();
              let newQty = data.newQty;
              button.setAttribute("data-qty", newQty);
              availableSpan.innerHTML = newQty > 0 ? `Available: ${newQty}` : "<span style='color:red;'>Out of Stock</span>";
              if (newQty <= 0) {
                button.disabled = true;
                if (qtyInput) qtyInput.disabled = true;
              }
              if (qtyInput) qtyInput.value = 1;
            } else {
              Swal.fire("Error", data.message, "error");
            }
          })
          .catch(error => {
            console.error("Fetch error:", error);
            Swal.fire("Error", "Something went wrong!", "error");
          });
        }
      });
    });

    function updateCartCount() {
      fetch("cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "action=count"
      })
      .then(response => response.json())
      .then(data => {
        document.getElementById("cart-count").innerText = data.cartCount || 0;
      })
      .catch(error => {
        console.error("Cart count error:", error);
      });
    }

    function toggleChat() {
    const chatWindow = document.getElementById("chatbot-window");
    chatWindow.style.display = chatWindow.style.display === "none" ? "block" : "none";
  }

  function sendMessage() {
    const input = document.getElementById("chat-input");
    const content = document.getElementById("chat-content");
    const message = input.value.trim();
    if (message !== "") {
      const userMsg = document.createElement("p");
      userMsg.textContent = "You: " + message;
      userMsg.style.fontWeight = "bold";
      userMsg.style.color = "white";
      content.appendChild(userMsg);

      fetch("chatbot.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "message=" + encodeURIComponent(message)
      })
      .then(response => response.text())
      .then(reply => {
        const botMsg = document.createElement("p");
        botMsg.textContent = "Bot: " + reply;
        botMsg.style.color = "white";
        content.appendChild(botMsg);
        content.scrollTop = content.scrollHeight;
      })
      .catch(() => {
        const errorMsg = document.createElement("p");
        errorMsg.textContent = "Bot: Sorry, something went wrong!";
        errorMsg.style.color = "red";
        content.appendChild(errorMsg);
      });

      input.value = "";
    }
  }

    function showFeedbackForm() {
      Swal.fire({
        title: 'Give us your feedback',
        input: 'textarea',
        inputPlaceholder: 'Write your feedback here...',
        showCancelButton: true,
        confirmButtonText: 'Send Feedback',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
          if (!value) return 'Please write something!';
        }
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire('Thank you!', 'Your feedback has been submitted.', 'success');
        }
      });
    }
    function openModal(src) {
      const modal = document.createElement('div');
      modal.style.position = 'fixed';
      modal.style.top = 0;
      modal.style.left = 0;
      modal.style.width = '100%';
      modal.style.height = '100%';
      modal.style.background = 'rgba(0, 0, 0, 0.8)';
      modal.style.display = 'flex';
      modal.style.alignItems = 'center';
      modal.style.justifyContent = 'center';
      modal.style.zIndex = 9999;
      modal.innerHTML = `
        <span style="position: absolute; top: 20px; right: 40px; font-size: 40px; color: white; cursor: pointer;">&times;</span>
        <img src="${src}" style="max-width: 80%; max-height: 80%;">
      `;
      modal.querySelector('span').onclick = () => modal.remove();
      document.body.appendChild(modal);
    }

  </script>

</body>
</html>