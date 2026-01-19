import path from 'path';
import express from 'express';

export function setupStaticServing(app) {
	  const frontendPath = path.join(process.cwd(), 'dist');

	    console.log('Serving frontend from:', frontendPath);

	      app.use(express.static(frontendPath));

	        app.get('*', (req, res) => {
			    res.sendFile(path.join(frontendPath, 'index.html'));
			      });
}

