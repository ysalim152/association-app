import express from 'express';
import dotenv from 'dotenv';
import { setupStaticServing } from './static-serve.js';
import { db } from './db/index.js';

dotenv.config();

const app = express();

// Body parsing middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Members API
app.get('/api/members', async (req, res) => {
  try {
    console.log('Fetching all members');
    const members = await db.selectFrom('members').selectAll().execute();
    console.log('Found members:', members.length);
    res.json(members);
  } catch (error) {
    console.error('Error fetching members:', error);
    res.status(500).json({ error: 'Failed to fetch members' });
  }
});

app.post('/api/members', async (req, res) => {
  try {
    console.log('Creating new member:', req.body);
    const { first_name, last_name, email, phone, membership_type } = req.body;
    
    const member = await db
      .insertInto('members')
      .values({
        first_name,
        last_name,
        email,
        phone,
        membership_type,
        join_date: new Date().toISOString(),
        status: 'active'
      })
      .returningAll()
      .executeTakeFirstOrThrow();
    
    console.log('Created member:', member);
    res.json(member);
  } catch (error) {
    console.error('Error creating member:', error);
    res.status(500).json({ error: 'Failed to create member' });
  }
});

// Teams API
app.get('/api/teams', async (req, res) => {
  try {
    console.log('Fetching all teams');
    const teams = await db.selectFrom('teams').selectAll().execute();
    console.log('Found teams:', teams.length);
    res.json(teams);
  } catch (error) {
    console.error('Error fetching teams:', error);
    res.status(500).json({ error: 'Failed to fetch teams' });
  }
});

app.post('/api/teams', async (req, res) => {
  try {
    console.log('Creating new team:', req.body);
    const { name, sport, category, coach_name, description } = req.body;
    
    const team = await db
      .insertInto('teams')
      .values({
        name,
        sport,
        category,
        coach_name,
        description
      })
      .returningAll()
      .executeTakeFirstOrThrow();
    
    console.log('Created team:', team);
    res.json(team);
  } catch (error) {
    console.error('Error creating team:', error);
    res.status(500).json({ error: 'Failed to create team' });
  }
});

// Events API
app.get('/api/events', async (req, res) => {
  try {
    console.log('Fetching all events');
    const events = await db.selectFrom('events').selectAll().execute();
    console.log('Found events:', events.length);
    res.json(events);
  } catch (error) {
    console.error('Error fetching events:', error);
    res.status(500).json({ error: 'Failed to fetch events' });
  }
});

app.post('/api/events', async (req, res) => {
  try {
    console.log('Creating new event:', req.body);
    const { title, description, event_type, start_date, end_date, location, team_id } = req.body;
    
    const event = await db
      .insertInto('events')
      .values({
        title,
        description,
        event_type,
        start_date,
        end_date,
        location,
        team_id
      })
      .returningAll()
      .executeTakeFirstOrThrow();
    
    console.log('Created event:', event);
    res.json(event);
  } catch (error) {
    console.error('Error creating event:', error);
    res.status(500).json({ error: 'Failed to create event' });
  }
});

// Export a function to start the server
export async function startServer(port) {
  try {
      setupStaticServing(app);
      app.listen(port, "0.0.0.0", () => {
      console.log(`API Server running on port ${port}`);
    });
  } catch (err) {
    console.error('Failed to start server:', err);
    process.exit(1);
  }
}

// Start the server directly if this is the main module
if (import.meta.url === `file://${process.argv[1]}`) {
  console.log('Starting server...');
  startServer(process.env.PORT || 3000);
}
