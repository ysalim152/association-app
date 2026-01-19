import express from "express";
import path from "path";

const app = express();

const clientPath = path.join(__dirname, "..", "client", "dist");

app.use(express.static(clientPath));

app.get("*", (req, res) => {
	  res.sendFile(path.join(clientPath, "index.html"));
});

export default app;

