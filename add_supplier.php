<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SweetBites - Add Supplier</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

    <div class="dashboard">


        <!-- SIDEBAR -->
        <aside class="sidebar">


            <!-- LOGO -->
            <div class="logo">

                <div class="logo-icon">
                    🍰
                </div>

                <div>

                    <h2>SweetBites</h2>

                    <p>
                        Inventory System
                    </p>

                </div>

            </div>


            <!-- NAVIGATION -->
            <nav>

                <a href="#" class="nav-item">

                    <span>⌂</span>

                    Dashboard

                </a>


                <a href="#" class="nav-item">

                    <span>🥣</span>

                    Ingredient Stock

                </a>


                <a
                    href="supplier.php"
                    class="nav-item active"
                >

                    <span>🚚</span>

                    Supplier

                </a>


                <a href="#" class="nav-item">

                    <span>📋</span>

                    Purchasing Order

                </a>


                <a href="#" class="nav-item">

                    <span>🎂</span>

                    Product Catalog

                </a>

            </nav>

        </aside>


        <!-- MAIN CONTENT -->
        <main class="main-content">


            <!-- TOP BAR -->
            <header class="topbar">

                <div>

                    <h1>
                        Add New Supplier
                    </h1>

                    <p>
                        Add supplier information to SweetBites
                    </p>

                </div>


                <div class="admin-profile">

                    <div class="profile-circle">
                        A
                    </div>

                    <div>

                        <strong>
                            Administrator
                        </strong>

                        <small>
                            Admin
                        </small>

                    </div>

                </div>

            </header>


            <!-- CONTENT -->
            <section class="content">


                <!-- PAGE HEADER -->
                <div class="page-header">

                    <div>

                        <h2>
                            Supplier Information
                        </h2>

                        <p>
                            Enter the details of the new supplier below.
                        </p>

                    </div>

                </div>


                <!-- FORM CARD -->
                <div class="form-card">


                    <form
                        action="save_supplier.php"
                        method="POST"
                    >


                        <!-- SUPPLIER NAME -->
                        <div class="form-group">

                            <label for="supplier_name">
                                Supplier Name
                            </label>

                            <input
                                type="text"
                                id="supplier_name"
                                name="supplier_name"
                                placeholder="Enter supplier name"
                                required
                            >

                        </div>


                        <!-- CONTACT PERSON -->
                        <div class="form-group">

                            <label for="contact_person">
                                Contact Person
                            </label>

                            <input
                                type="text"
                                id="contact_person"
                                name="contact_person"
                                placeholder="Enter contact person"
                            >

                        </div>


                        <!-- PHONE -->
                        <div class="form-group">

                            <label for="phone">
                                Phone Number
                            </label>

                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                placeholder="Enter phone number"
                            >

                        </div>


                        <!-- EMAIL -->
                        <div class="form-group">

                            <label for="email">
                                Email
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter email address"
                            >

                        </div>


                        <!-- ADDRESS -->
                        <div class="form-group">

                            <label for="address">
                                Address
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                placeholder="Enter supplier address"
                                rows="4"
                            ></textarea>

                        </div>


                        <!-- STATUS -->
                        <div class="form-group">

                            <label for="status">
                                Status
                            </label>

                            <select
                                id="status"
                                name="status"
                            >

                                <option value="Active">
                                    Active
                                </option>

                                <option value="Inactive">
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <!-- BUTTONS -->
                        <div class="form-actions">


                            <a
                                href="supplier.php"
                                class="cancel-button"
                            >
                                Cancel
                            </a>


                            <button
                                type="submit"
                                class="save-button"
                            >
                                Add Supplier
                            </button>


                        </div>


                    </form>

                </div>

            </section>

        </main>

    </div>

</body>

</html>