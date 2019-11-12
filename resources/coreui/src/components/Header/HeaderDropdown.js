import React, {Component} from 'react';
import axios from 'axios';
import {
  Badge,
  DropdownItem,
  DropdownMenu,
  DropdownToggle,
  Dropdown
} from 'reactstrap';

class HeaderDropdown extends Component {

  constructor(props) {
    super(props);

    this.toggle = this.toggle.bind(this);
    this.state = {
      dropdownOpen: false,
      user_name: '',
    };
  }

  toggle() {
    this.setState({
      dropdownOpen: !this.state.dropdownOpen
    });
  }

  componentDidMount(){
    this.setState({
      user_name: window.user_name
    });
  }

  logout(){
    if(confirm("Are you sure you want to log out?")) {
     
      axios.post('/logout', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        
      })
      .then(function (response) {
        console.log('Logout!')
        console.log(response)
        window.location.href='/'
      })
     .catch(error => {
       // If the api request failed then you still might want to remove
       // the same data from localStorage anyways
       // perhaps this code should go in a finally method instead of then and catch
       // methods to avoid duplication.
       console.log('error logout')
     });       
    }
   
  }

  dropAccnt() {
    return (
      <Dropdown nav isOpen={this.state.dropdownOpen} toggle={this.toggle}>
        <DropdownToggle nav>
          <img src={'img/avatars/6.jpg'} className="img-avatar" alt=""/>
          {this.state.user_name}
        </DropdownToggle>
        <DropdownMenu right>
          <DropdownItem header tag="div" className="text-center"><strong>Account</strong></DropdownItem>
          <DropdownItem><i className="fa fa-bell-o"></i> Updates<Badge color="info">42</Badge></DropdownItem>
          <DropdownItem><i className="fa fa-envelope-o"></i> Messages<Badge color="success">42</Badge></DropdownItem>
          <DropdownItem><i className="fa fa-tasks"></i> Tasks<Badge color="danger">42</Badge></DropdownItem>
          <DropdownItem><i className="fa fa-comments"></i> Comments<Badge color="warning">42</Badge></DropdownItem>
          <DropdownItem header tag="div" className="text-center"><strong>Settings</strong></DropdownItem>
          <DropdownItem><i className="fa fa-user"></i> Profile</DropdownItem>
          <DropdownItem><i className="fa fa-wrench"></i> Settings</DropdownItem>
          <DropdownItem><i className="fa fa-usd"></i> Payments<Badge color="secondary">42</Badge></DropdownItem>
          <DropdownItem><i className="fa fa-file"></i> Projects<Badge color="primary">42</Badge></DropdownItem>
          <DropdownItem divider/>
          <DropdownItem><i className="fa fa-shield"></i> Lock Account</DropdownItem>
          <DropdownItem onClick={this.logout}><i className="fa fa-lock"></i> Logout</DropdownItem>
        </DropdownMenu>
      </Dropdown>
    );
  }

  render() {
    const {...attributes} = this.props;
    return (
      this.dropAccnt()
    );
  }
}

export default HeaderDropdown;
