<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// class CreatePurchaseItemsTable extends Migration
// {
//     public function up()
//     {
//         Schema::create('purchase_items', function (Blueprint $table) {
//             $table->id();
//             $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
//             $table->foreignId('product_id')->constrained()->onDelete('cascade');
//             $table->integer('qty');
//             $table->integer('harga'); 
//             $table->timestamps();
//         });
//     }

//     public function down()
//     {
//         Schema::dropIfExists('purchase_items');
//     }
// }
