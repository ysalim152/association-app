import * as React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, MapPin, Clock } from 'lucide-react';

interface Event {
  id: number;
  title: string;
  description: string | null;
  event_type: string;
  start_date: string;
  end_date: string | null;
  location: string | null;
  team_id: number | null;
  created_at: string;
}

interface EventsListProps {
  refreshTrigger: number;
}

export function EventsList({ refreshTrigger }: EventsListProps) {
  const [events, setEvents] = React.useState<Event[]>([]);
  const [loading, setLoading] = React.useState(true);

  React.useEffect(() => {
    const fetchEvents = async () => {
      try {
        const response = await fetch('/api/events');
        const data = await response.json();
        setEvents(data);
      } catch (error) {
        console.error('Error fetching events:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchEvents();
  }, [refreshTrigger]);

  if (loading) {
    return <div>Loading events...</div>;
  }

  if (events.length === 0) {
    return (
      <Card>
        <CardContent className="p-6">
          <div className="text-center text-muted-foreground">
            No events found. Schedule your first event to get started.
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {events.map((event) => (
        <Card key={event.id}>
          <CardHeader>
            <CardTitle className="text-lg">{event.title}</CardTitle>
            <Badge variant="secondary">{event.event_type}</Badge>
          </CardHeader>
          <CardContent className="space-y-3">
            {event.description && (
              <div className="text-sm text-muted-foreground">
                {event.description}
              </div>
            )}
            <div className="flex items-center space-x-2 text-sm">
              <Calendar className="h-4 w-4" />
              <span>{new Date(event.start_date).toLocaleDateString()}</span>
            </div>
            {event.end_date && (
              <div className="flex items-center space-x-2 text-sm">
                <Clock className="h-4 w-4" />
                <span>
                  {new Date(event.start_date).toLocaleTimeString()} - {new Date(event.end_date).toLocaleTimeString()}
                </span>
              </div>
            )}
            {event.location && (
              <div className="flex items-center space-x-2 text-sm">
                <MapPin className="h-4 w-4" />
                <span>{event.location}</span>
              </div>
            )}
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
