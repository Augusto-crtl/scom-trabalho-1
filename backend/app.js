// backend/app.js
const express = require('express');
const cors = require('cors');

const app = express();

app.use(cors()); // Libera requisições do frontend
app.use(express.json());
