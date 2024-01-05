<h3>What does it do?</h3>
  <p>This module is a module for turning a filled cart into a completed Order.  This module handles collecting billing information and shipping information from the user and then either using an external payment gateway to handle payment information, or performing direct email based invoicing.</p>
  <p>The orders module keeps track of all orders, and allows specifying items like tracking numbers and confirmation numbers, and also allows sending emails to the client pertaining to a particular order.</p>

<h3>How Do I use It?</h3>
<p>This module works in conjunction with the cart module as selected in the EcommerceExt module.  Create a page, call it "Checkout" and set it's content should be "{EcOrderMgr}".  This page will start the Checkout process.</p>

<h3><span style="color: red;">Warning - Credit Card Information</span></h3>
<p>It is not recommended that you use the credit card number gathering mechanism unless using a secure communications mechanism and only on secured, dedicated hosts.  There is no guarantee that the database numbers (though a simple encryption mechanism is used) are not vulnerable to theft.</p>

<h3>Order Calculations:</h3>
<p>Orders are built after the billing and shipping information is provided by the user.  This is because billing and shipping information may be needed to calculate taxes and shipping costs.</p>
<p>When orders are calculated, the cart is copied into line item objects.  Then, the promotions module (if installed and configured) is called to see if any promotions apply to the current cart.  If so some line items are adjusted or new line items added depending upon the offer associated with the promotion.</p>
<p>At this time, shipping and handling costs are calculated (if appropriate modules are installed and configured) once per destination.  Representing in a new line item for each shipment.</p>
<p>Next, taxes are calculated and added as one or more line items (if a tax module is installed and configured).  The tax module can decide wether or not to tax shipping items.</p>
<p>Some final assertions are done on the order:</p>
<ul>
  <li>Discounts are never allowed to exceed the value of the items purchased (not including shipping costs).</li>
  <li>The order total is never allowed to be negative.</li>
</ul>

<h3>Support</h3>
<p>The module author is in no way obligated to privide support for this code in any fashion.  However, there are a number of resources available to help you with it:</p>
<ul>
<li>A bug tracking and feature request system has been created for this module <a href="http://dev.cmsmadesimple.org/projects/orders">here</a>.  Please be verbose and descriptive when submitting bug reports and feature requests, and for bug reports ensure that you have provided sufficient information to reliably reproduce the issue.</li>
<li>Additional discussion of this module may also be found in the <a href="http://forum.cmsmadesimple.org">CMS Made Simple Forums</a>.  When describing an issue please make an effort to privide all relavant information, a thorough description of your issue, and steps to reproduce it or your discussion may be ignored.</li>
<li>The author, calguy1000, can often be found in the <a href="irc://irc.freenode.net/#cms">CMS IRC Channel</a>.</li>
<li>Lastly, you may have some success emailing the author directly.  However, please use this as a last resort, and ensure that you have followed all applicable instructions on the forge, in the forums, etc.</li>
</ul>

<br/>

<h3>Copyright and License</h3>
<p>Copyright &copy; 2008, Robert Campbel <a href="mailto:calguy1000@cmsmadesimple.org">&lt;calguy1000@cmsmadesimple.org&gt;</a>. All Rights Are Reserved.</p>
<p>This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.</p>
<p>However, as a special exception to the GPL, this software is distributed
as an addon module to CMS Made Simple.  You may not use this software
in any Non GPL version of CMS Made simple, or in any version of CMS
Made simple that does not indicate clearly and obviously in its admin
section that the site was built with CMS Made simple.</p>
<p>This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
Or read it <a href="http://www.gnu.org/licenses/licenses.html#GPL">online</a></p>
