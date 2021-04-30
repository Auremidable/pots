import React from 'react';
import {
  BrowserRouter as Router,
  Switch,
  Route
} from "react-router-dom";
import LoginComponent from './Components/LoginComponent.jsx';
import SignupComponent from './Components/SignupComponent.jsx';
import EventsCardComponent from './Components/EventsCardComponent.jsx';
import FormNewEventComponent from './Components/FormNewEventComponent.jsx';
import ProtectedRoute from './AuthHelpers/ProtectedRoute';

import './assets/styles/styles.css';

function App() {
  return (
    <Router>
      <Switch>
        <Route exact path="/">
          <LoginComponent />
        </Route>
        <Route path="/signup">
          <SignupComponent />
        </Route>
        <ProtectedRoute path="/home" component={ EventsCardComponent } />        
        <Route path="/newevent">
          <FormNewEventComponent />
        </Route>
      </Switch>
    </Router>
  );
}

export default App;
