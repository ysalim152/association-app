import * as React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { Layout } from './components/Layout';
import { Dashboard } from './pages/Dashboard';
import { MembersPage } from './pages/members/MembersPage';
import { TeamsPage } from './pages/teams/TeamsPage';
import { EventsPage } from './pages/events/EventsPage';

function App() {
  return (
    <Router>
      <Layout>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/members" element={<MembersPage />} />
          <Route path="/teams" element={<TeamsPage />} />
          <Route path="/events" element={<EventsPage />} />
        </Routes>
      </Layout>
    </Router>
  );
}

export default App;
